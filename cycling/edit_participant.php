<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$errors  = [];
$success = false;
$participant = null;

$conn = getConnection();

// ── Handle search by ID ───────────────────────────────────────────────────────
$searchId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($searchId > 0) {
    $stmt = $conn->prepare(
        'SELECT id, first_name, last_name, power_output, distance FROM participant WHERE id = ? LIMIT 1'
    );
    $stmt->bind_param('i', $searchId);
    $stmt->execute();
    $result = $stmt->get_result();
    $participant = $result->fetch_assoc();
    $stmt->close();
    if (!$participant) {
        $errors['search'] = 'No participant found with that ID.';
    }
}

// ── Handle update ─────────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $pid          = (int)($_POST['participant_id'] ?? 0);
    $powerOutput  = trim($_POST['power_output'] ?? '');
    $distance     = trim($_POST['distance']      ?? '');

    if ($pid <= 0) {
        $errors['form'] = 'Invalid participant.';
    }
    if ($powerOutput === '') {
        $errors['power_output'] = 'Power output is required.';
    } elseif (!is_numeric($powerOutput) || (float)$powerOutput < 0) {
        $errors['power_output'] = 'Power output must be a positive number.';
    }
    if ($distance === '') {
        $errors['distance'] = 'Distance is required.';
    } elseif (!is_numeric($distance) || (float)$distance < 0) {
        $errors['distance'] = 'Distance must be a positive number.';
    }

    if (empty($errors)) {
        $stmt = $conn->prepare(
            'UPDATE participant SET power_output = ?, distance = ? WHERE id = ?'
        );
        $po = (float)$powerOutput;
        $di = (float)$distance;
        $stmt->bind_param('ddi', $po, $di, $pid);
        if ($stmt->execute() && $stmt->affected_rows >= 0) {
            $success = true;
            // Reload fresh data
            $stmt2 = $conn->prepare(
                'SELECT id, first_name, last_name, power_output, distance FROM participant WHERE id = ? LIMIT 1'
            );
            $stmt2->bind_param('i', $pid);
            $stmt2->execute();
            $participant = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
        } else {
            $errors['form'] = 'Failed to update participant. Please try again.';
        }
        $stmt->close();
    } else {
        // Re-fetch participant so the form can redisplay
        if ($pid > 0) {
            $stmt = $conn->prepare(
                'SELECT id, first_name, last_name, power_output, distance FROM participant WHERE id = ? LIMIT 1'
            );
            $stmt->bind_param('i', $pid);
            $stmt->execute();
            $participant = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        }
    }
}

// All participants for the dropdown
$allParticipants = $conn->query('SELECT id, first_name, last_name FROM participant ORDER BY last_name, first_name');
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Participant Scores – Cit-E Cycling</title>
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
                <li class="nav-item"><a class="nav-link active" href="edit_participant.php">Edit Scores</a></li>
                <li class="nav-item"><a class="nav-link" href="delete_participant.php">Delete Participant</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold mb-1"><i class="bi bi-pencil-square"></i> Edit Participant Scores</h2>
    <p class="text-muted mb-4">Select a participant then update their power output and distance.</p>

    <!-- Step 1: Select participant -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white fw-semibold">Step 1 – Select Participant</div>
        <div class="card-body p-4">
            <form method="get" action="edit_participant.php" class="row g-3 align-items-end">
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
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Load Participant
                    </button>
                </div>
            </form>
            <?php if (isset($errors['search'])): ?>
            <div class="alert alert-danger mt-3 mb-0">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errors['search']) ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Step 2: Edit scores -->
    <?php if ($participant): ?>
    <div class="card shadow-sm border-0">
        <div class="card-header bg-success text-white fw-semibold">Step 2 – Update Scores</div>
        <div class="card-body p-4">

            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                Scores for <strong><?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?></strong> have been updated.
            </div>
            <?php endif; ?>

            <?php if (isset($errors['form'])): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errors['form']) ?>
            </div>
            <?php endif; ?>

            <p class="fw-semibold">
                Editing: <span class="text-primary"><?= htmlspecialchars($participant['first_name'] . ' ' . $participant['last_name']) ?></span>
                <span class="badge bg-secondary ms-1">ID: <?= $participant['id'] ?></span>
            </p>

            <form method="post" action="edit_participant.php?id=<?= $participant['id'] ?>" novalidate id="editForm">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="participant_id" value="<?= $participant['id'] ?>">

                <div class="row g-3">
                    <div class="col-sm-6">
                        <label for="power_output" class="form-label fw-semibold">
                            Power Output (watts) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0"
                               class="form-control <?= isset($errors['power_output']) ? 'is-invalid' : '' ?>"
                               id="power_output" name="power_output"
                               value="<?= htmlspecialchars($_POST['power_output'] ?? $participant['power_output']) ?>"
                               required>
                        <?php if (isset($errors['power_output'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['power_output']) ?></div>
                        <?php else: ?>
                        <div class="form-text">Current: <?= number_format($participant['power_output'], 2) ?> W</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-sm-6">
                        <label for="distance" class="form-label fw-semibold">
                            Distance (miles) <span class="text-danger">*</span>
                        </label>
                        <input type="number" step="0.01" min="0"
                               class="form-control <?= isset($errors['distance']) ? 'is-invalid' : '' ?>"
                               id="distance" name="distance"
                               value="<?= htmlspecialchars($_POST['distance'] ?? $participant['distance']) ?>"
                               required>
                        <?php if (isset($errors['distance'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['distance']) ?></div>
                        <?php else: ?>
                        <div class="form-text">Current: <?= number_format($participant['distance'], 2) ?> miles</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success me-2">
                        <i class="bi bi-save"></i> Save Changes
                    </button>
                    <a href="edit_participant.php" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> Choose Different Participant
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
    var form = document.getElementById('editForm');
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
