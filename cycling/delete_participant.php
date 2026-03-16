<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$errors      = [];
$success     = false;
$participant = null;

$conn = getConnection();

// ── Load participant for review ───────────────────────────────────────────────
$searchId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($searchId > 0) {
    $stmt = $conn->prepare(
        'SELECT p.id, p.first_name, p.last_name, p.age, p.gender, p.power_output, p.distance, c.club_name
         FROM participant p
         LEFT JOIN club c ON p.club_id = c.id
         WHERE p.id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $searchId);
    $stmt->execute();
    $participant = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$participant) {
        $errors['search'] = 'No participant found with that ID.';
    }
}

// ── Handle confirmed deletion ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $pid = (int)($_POST['participant_id'] ?? 0);
    // Re-verify confirmation checkbox
    $confirmed = isset($_POST['confirm_delete']) && $_POST['confirm_delete'] === '1';

    if ($pid <= 0) {
        $errors['form'] = 'Invalid participant ID.';
    } elseif (!$confirmed) {
        $errors['confirm'] = 'You must check the confirmation box before deleting.';
        // Re-load participant for the form
        $stmt = $conn->prepare(
            'SELECT p.id, p.first_name, p.last_name, p.age, p.gender, p.power_output, p.distance, c.club_name
             FROM participant p
             LEFT JOIN club c ON p.club_id = c.id
             WHERE p.id = ? LIMIT 1'
        );
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $participant = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $stmt = $conn->prepare('DELETE FROM participant WHERE id = ?');
        $stmt->bind_param('i', $pid);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            $success = true;
            $participant = null;
        } else {
            $errors['form'] = 'Failed to delete the participant. Please try again.';
        }
        $stmt->close();
    }
}

// All participants for dropdown
$allParticipants = $conn->query('SELECT id, first_name, last_name FROM participant ORDER BY last_name, first_name');
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Participant – Cit-E Cycling</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>body { background-color: #f8f9fa; } footer { background: #212529; color: #adb5bd; }</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php"><i class="bi bi-bicycle"></i> Cit-E Cycling</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item"><a class="nav-link" href="admin.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="edit_participant.php">Edit Scores</a></li>
                <li class="nav-item"><a class="nav-link active" href="delete_participant.php">Delete Participant</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold mb-1 text-danger"><i class="bi bi-trash"></i> Delete Participant</h2>
    <p class="text-muted mb-4">Select a participant, review their details, then confirm deletion.</p>

    <?php if ($success): ?>
    <div class="alert alert-success">
        <i class="bi bi-check-circle-fill"></i> Participant has been successfully deleted.
        <a href="delete_participant.php" class="alert-link ms-2">Delete another?</a>
    </div>
    <?php endif; ?>

    <?php if (isset($errors['form'])): ?>
    <div class="alert alert-danger">
        <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errors['form']) ?>
    </div>
    <?php endif; ?>

    <!-- Step 1: Select participant -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-secondary text-white fw-semibold">Step 1 – Select Participant</div>
        <div class="card-body p-4">
            <form method="get" action="delete_participant.php" class="row g-3 align-items-end">
                <div class="col-md-8">
                    <label for="id" class="form-label fw-semibold">Participant</label>
                    <select name="id" id="id" class="form-select" required>
                        <option value="">-- Select a participant --</option>
                        <?php while ($row = $allParticipants->fetch_assoc()): ?>
                        <option value="<?= $row['id'] ?>" <?= ($searchId === (int)$row['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($row['last_name'] . ', ' . $row['first_name']) ?> (ID: <?= $row['id'] ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-secondary w-100">
                        <i class="bi bi-search"></i> Load Participant
                    </button>
                </div>
            </form>
            <?php if (isset($errors['search'])): ?>
            <div class="alert alert-warning mt-3 mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errors['search']) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Step 2: Confirm deletion -->
    <?php if ($participant): ?>
    <div class="card shadow-sm border-0 border-danger">
        <div class="card-header bg-danger text-white fw-semibold">Step 2 – Confirm Deletion</div>
        <div class="card-body p-4">
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Warning:</strong> This action cannot be undone. Please review the details below carefully.
            </div>

            <table class="table table-bordered table-sm mb-4">
                <tbody>
                    <tr><th scope="row" class="table-light" style="width:35%">ID</th><td><?= $participant['id'] ?></td></tr>
                    <tr><th scope="row" class="table-light">Name</th>
                        <td><?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?></td></tr>
                    <tr><th scope="row" class="table-light">Age</th><td><?= $participant['age'] ?></td></tr>
                    <tr><th scope="row" class="table-light">Gender</th><td><?= htmlspecialchars($participant['gender']) ?></td></tr>
                    <tr><th scope="row" class="table-light">Power Output</th>
                        <td><?= number_format($participant['power_output'], 2) ?> W</td></tr>
                    <tr><th scope="row" class="table-light">Distance</th>
                        <td><?= number_format($participant['distance'], 2) ?> miles</td></tr>
                    <tr><th scope="row" class="table-light">Club</th>
                        <td><?= $participant['club_name'] ? htmlspecialchars($participant['club_name']) : '<em class="text-muted">Individual</em>' ?></td></tr>
                </tbody>
            </table>

            <form method="post" action="delete_participant.php" novalidate id="deleteForm">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">

                <div class="form-check mb-3">
                    <input class="form-check-input <?= isset($errors['confirm']) ? 'is-invalid' : '' ?>"
                           type="checkbox" name="confirm_delete" id="confirm_delete" value="1" required>
                    <label class="form-check-label fw-semibold" for="confirm_delete">
                        I confirm I want to permanently delete
                        <strong><?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?></strong>
                    </label>
                    <?php if (isset($errors['confirm'])): ?>
                    <div class="invalid-feedback"><?= htmlspecialchars($errors['confirm']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-danger" id="deleteBtn" disabled>
                        <i class="bi bi-trash-fill"></i> Delete Participant
                    </button>
                    <a href="delete_participant.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>
</div>

<footer class="py-4 text-center mt-5">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> Cit-E Cycling. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    'use strict';
    var checkbox = document.getElementById('confirm_delete');
    var btn      = document.getElementById('deleteBtn');
    if (checkbox && btn) {
        checkbox.addEventListener('change', function () {
            btn.disabled = !this.checked;
        });
    }
    var form = document.getElementById('deleteForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }
})();
</script>
</body>
</html>
