<?php
require_once 'db.php';

$searchType       = isset($_GET['type']) ? $_GET['type'] : 'participant';
$searchQuery      = trim($_GET['q'] ?? '');
$participantResults = [];
$clubResults        = [];
$searchPerformed    = false;
$errors             = [];

if (isset($_GET['q']) || isset($_GET['type'])) {
    $searchPerformed = true;

    if ($searchQuery === '') {
        $errors['q'] = 'Please enter a search term.';
    } else {
        $conn = getConnection();

        if ($searchType === 'participant') {
            // Search by first name OR last name
            $like = '%' . $searchQuery . '%';
            $stmt = $conn->prepare(
                'SELECT p.id, p.first_name, p.last_name, p.age, p.gender,
                        p.power_output, p.distance, c.club_name
                 FROM participant p
                 LEFT JOIN club c ON p.club_id = c.id
                 WHERE p.first_name LIKE ? OR p.last_name LIKE ?
                 ORDER BY p.last_name, p.first_name'
            );
            $stmt->bind_param('ss', $like, $like);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $participantResults[] = $row;
            }
            $stmt->close();
        } else {
            // Search clubs by name; fetch all club info and members in two queries
            $like = '%' . $searchQuery . '%';

            // Query 1: Club aggregates
            $stmt = $conn->prepare(
                'SELECT c.id AS club_id, c.club_name,
                        COUNT(p.id)           AS member_count,
                        SUM(p.distance)       AS total_distance,
                        AVG(p.distance)       AS avg_distance,
                        SUM(p.power_output)   AS total_power,
                        AVG(p.power_output)   AS avg_power
                 FROM club c
                 LEFT JOIN participant p ON c.id = p.club_id
                 WHERE c.club_name LIKE ?
                 GROUP BY c.id, c.club_name
                 ORDER BY c.club_name'
            );
            $stmt->bind_param('s', $like);
            $stmt->execute();
            $clubRows = $stmt->get_result();
            $stmt->close();

            $clubIndex = [];
            while ($clubRow = $clubRows->fetch_assoc()) {
                $clubRow['members'] = [];
                $clubIndex[$clubRow['club_id']] = $clubRow;
            }

            if (!empty($clubIndex)) {
                // Query 2: All members belonging to the matched clubs in one query
                $placeholders = implode(',', array_fill(0, count($clubIndex), '?'));
                $types        = str_repeat('i', count($clubIndex));
                $ids          = array_keys($clubIndex);
                $stmt2 = $conn->prepare(
                    'SELECT id, first_name, last_name, age, gender, power_output, distance, club_id
                     FROM participant
                     WHERE club_id IN (' . $placeholders . ')
                     ORDER BY last_name, first_name'
                );
                $stmt2->bind_param($types, ...$ids);
                $stmt2->execute();
                $memberResult = $stmt2->get_result();
                while ($m = $memberResult->fetch_assoc()) {
                    $clubIndex[$m['club_id']]['members'][] = $m;
                }
                $stmt2->close();
            }

            $clubResults = array_values($clubIndex);
        }

        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search – Cit-E Cycling</title>
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
                <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="register_interest.php">Register Interest</a></li>
                <li class="nav-item"><a class="nav-link active" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Admin Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <h2 class="fw-bold mb-1"><i class="bi bi-search"></i> Search</h2>
    <p class="text-muted mb-4">Search for participants by name, or clubs by club name.</p>

    <!-- Search Form -->
    <div class="card shadow-sm border-0 mb-5">
        <div class="card-body p-4">
            <form method="get" action="search.php" novalidate id="searchForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Search Type</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="typeParticipant"
                                       value="participant" <?= ($searchType !== 'club') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="typeParticipant">Participant</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="type" id="typeClub"
                                       value="club" <?= ($searchType === 'club') ? 'checked' : '' ?>>
                                <label class="form-check-label" for="typeClub">Club</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <label for="q" class="form-label fw-semibold">
                            <span id="searchLabel"><?= ($searchType === 'club') ? 'Club Name' : 'First or Last Name' ?></span>
                            <span class="text-danger">*</span>
                        </label>
                        <input type="search" class="form-control <?= isset($errors['q']) ? 'is-invalid' : '' ?>"
                               id="q" name="q"
                               value="<?= htmlspecialchars($searchQuery) ?>"
                               placeholder="Enter search term…" required>
                        <?php if (isset($errors['q'])): ?>
                        <div class="invalid-feedback"><?= htmlspecialchars($errors['q']) ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Participant Results -->
    <?php if ($searchPerformed && $searchType === 'participant' && empty($errors)): ?>
    <h4 class="fw-bold mb-3">
        Participant Results
        <span class="badge bg-primary ms-1"><?= count($participantResults) ?></span>
    </h4>

    <?php if (empty($participantResults)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle-fill"></i>
        No participants found matching "<strong><?= htmlspecialchars($searchQuery) ?></strong>".
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle shadow-sm">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Age</th>
                    <th>Gender</th>
                    <th>Power Output (W)</th>
                    <th>Distance (miles)</th>
                    <th>Club</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participantResults as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($p['first_name'] . ' ' . $p['last_name']) ?></td>
                    <td><?= $p['age'] ?></td>
                    <td><?= htmlspecialchars($p['gender']) ?></td>
                    <td><?= number_format($p['power_output'], 2) ?></td>
                    <td><?= number_format($p['distance'], 2) ?></td>
                    <td><?= $p['club_name'] ? htmlspecialchars($p['club_name']) : '<em class="text-muted">Individual</em>' ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
    <?php endif; ?>

    <!-- Club Results -->
    <?php if ($searchPerformed && $searchType === 'club' && empty($errors)): ?>
    <h4 class="fw-bold mb-3">
        Club Results
        <span class="badge bg-info text-dark ms-1"><?= count($clubResults) ?></span>
    </h4>

    <?php if (empty($clubResults)): ?>
    <div class="alert alert-info">
        <i class="bi bi-info-circle-fill"></i>
        No clubs found matching "<strong><?= htmlspecialchars($searchQuery) ?></strong>".
    </div>
    <?php else: ?>
    <?php foreach ($clubResults as $club): ?>
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-info text-dark fw-bold">
            <i class="bi bi-people-fill"></i> <?= htmlspecialchars($club['club_name']) ?>
            <span class="badge bg-dark ms-2"><?= $club['member_count'] ?> member<?= $club['member_count'] != 1 ? 's' : '' ?></span>
        </div>
        <div class="card-body p-0">
            <!-- Club Aggregates -->
            <div class="row g-0 text-center border-bottom">
                <div class="col-sm-3 p-3 border-end">
                    <p class="text-muted mb-1 small">Total Distance</p>
                    <p class="fw-bold mb-0"><?= number_format($club['total_distance'] ?? 0, 2) ?> miles</p>
                </div>
                <div class="col-sm-3 p-3 border-end">
                    <p class="text-muted mb-1 small">Avg Distance</p>
                    <p class="fw-bold mb-0"><?= number_format($club['avg_distance'] ?? 0, 2) ?> miles</p>
                </div>
                <div class="col-sm-3 p-3 border-end">
                    <p class="text-muted mb-1 small">Total Power Output</p>
                    <p class="fw-bold mb-0"><?= number_format($club['total_power'] ?? 0, 2) ?> W</p>
                </div>
                <div class="col-sm-3 p-3">
                    <p class="text-muted mb-1 small">Avg Power Output</p>
                    <p class="fw-bold mb-0"><?= number_format($club['avg_power'] ?? 0, 2) ?> W</p>
                </div>
            </div>

            <!-- Club Members -->
            <?php if (!empty($club['members'])): ?>
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Gender</th>
                            <th>Power Output (W)</th>
                            <th>Distance (miles)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($club['members'] as $m): ?>
                        <tr>
                            <td><?= $m['id'] ?></td>
                            <td class="fw-semibold"><?= htmlspecialchars($m['first_name'] . ' ' . $m['last_name']) ?></td>
                            <td><?= $m['age'] ?></td>
                            <td><?= htmlspecialchars($m['gender']) ?></td>
                            <td><?= number_format($m['power_output'], 2) ?></td>
                            <td><?= number_format($m['distance'], 2) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <p class="text-muted p-3 mb-0">This club has no current participants.</p>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
    <?php endif; ?>

</div>

<footer class="py-4 text-center mt-auto">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> Cit-E Cycling. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    'use strict';

    // Update placeholder label when search type changes
    var radios = document.querySelectorAll('input[name="type"]');
    var label  = document.getElementById('searchLabel');
    radios.forEach(function (r) {
        r.addEventListener('change', function () {
            label.textContent = this.value === 'club' ? 'Club Name' : 'First or Last Name';
        });
    });

    // Client-side validation
    var form = document.getElementById('searchForm');
    form.addEventListener('submit', function (e) {
        if (!form.checkValidity()) {
            e.preventDefault();
            e.stopPropagation();
        }
        form.classList.add('was-validated');
    }, false);
})();
</script>
</body>
</html>
