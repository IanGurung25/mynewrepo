<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$conn = getConnection();
$result = $conn->query('SELECT COUNT(*) AS total FROM participant');
$participantCount = $result->fetch_assoc()['total'];

$result2 = $conn->query('SELECT COUNT(*) AS total FROM interest');
$interestCount = $result2->fetch_assoc()['total'];

$result3 = $conn->query('SELECT COUNT(*) AS total FROM club');
$clubCount = $result3->fetch_assoc()['total'];
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – Cit-E Cycling</title>
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
                <li class="nav-item"><a class="nav-link active" href="admin.php">Dashboard</a></li>
                <li class="nav-item"><a class="nav-link" href="edit_participant.php">Edit Scores</a></li>
                <li class="nav-item"><a class="nav-link" href="delete_participant.php">Delete Participant</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link text-warning" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="fw-bold mb-0"><i class="bi bi-speedometer2"></i> Admin Dashboard</h2>
            <p class="text-muted">Welcome back, <strong><?= htmlspecialchars($_SESSION['admin_user']) ?></strong></p>
        </div>
        <a href="logout.php" class="btn btn-outline-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-5">
        <div class="col-sm-4">
            <div class="card text-white bg-primary shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-person-lines-fill fs-1 me-3"></i>
                    <div>
                        <p class="mb-0 small text-uppercase fw-semibold">Participants</p>
                        <h3 class="mb-0 fw-bold"><?= $participantCount ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-white bg-success shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-clipboard2-check fs-1 me-3"></i>
                    <div>
                        <p class="mb-0 small text-uppercase fw-semibold">Registrations</p>
                        <h3 class="mb-0 fw-bold"><?= $interestCount ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-white bg-info shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <i class="bi bi-people-fill fs-1 me-3"></i>
                    <div>
                        <p class="mb-0 small text-uppercase fw-semibold">Clubs</p>
                        <h3 class="mb-0 fw-bold"><?= $clubCount ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick-access cards -->
    <h5 class="fw-bold mb-3">Quick Actions</h5>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <i class="bi bi-pencil-square text-primary" style="font-size:2.5rem;"></i>
                    <h5 class="fw-bold mt-3">Edit Participant Scores</h5>
                    <p class="text-muted">Update a participant's power output and distance.</p>
                    <a href="edit_participant.php" class="btn btn-primary">Go to Edit</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <i class="bi bi-trash text-danger" style="font-size:2.5rem;"></i>
                    <h5 class="fw-bold mt-3">Delete Participant</h5>
                    <p class="text-muted">Remove a participant from the competition.</p>
                    <a href="delete_participant.php" class="btn btn-danger">Go to Delete</a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <i class="bi bi-search text-success" style="font-size:2.5rem;"></i>
                    <h5 class="fw-bold mt-3">Search</h5>
                    <p class="text-muted">Search for participants or cycling clubs.</p>
                    <a href="search.php" class="btn btn-success">Go to Search</a>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="py-4 text-center mt-5">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> Cit-E Cycling. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
