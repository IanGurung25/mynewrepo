<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cit-E Cycling</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .hero { background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%); color: #fff; padding: 80px 0; }
        .feature-icon { font-size: 2.5rem; color: #0d6efd; }
        footer { background: #212529; color: #adb5bd; }
    </style>
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
                <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="register_interest.php">Register Interest</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Admin Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- Hero -->
<section class="hero text-center">
    <div class="container">
        <h1 class="display-4 fw-bold"><i class="bi bi-bicycle"></i> Cit-E Cycling</h1>
        <p class="lead mt-3 mb-4">The UK's premier urban cycling competition. Compete, connect, and conquer.</p>
        <a href="register_interest.php" class="btn btn-light btn-lg me-2">Register Your Interest</a>
        <a href="search.php" class="btn btn-outline-light btn-lg">Find Participants</a>
    </div>
</section>

<!-- Features -->
<section class="py-5">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-4">
                    <div class="feature-icon mb-3"><i class="bi bi-trophy"></i></div>
                    <h5 class="fw-bold">10 Pop-Up Tournaments</h5>
                    <p class="text-muted">Held across the city in different locations, each event lasting 3 days.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-4">
                    <div class="feature-icon mb-3"><i class="bi bi-lightning-charge"></i></div>
                    <h5 class="fw-bold">20 High-Tech Bikes</h5>
                    <p class="text-muted">Track your miles and power output. Compete individually or as a group.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card h-100 shadow-sm border-0 p-4">
                    <div class="feature-icon mb-3"><i class="bi bi-people"></i></div>
                    <h5 class="fw-bold">Multiple Categories</h5>
                    <p class="text-muted">Prizes for age categories, genders and the best-performing cycling group.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<section class="bg-primary text-white py-5 text-center">
    <div class="container">
        <h2 class="fw-bold">Ready to ride?</h2>
        <p class="lead">Register your interest now and secure your slot at a Cit-E Cycling event near you.</p>
        <a href="register_interest.php" class="btn btn-light btn-lg">Register Now</a>
    </div>
</section>

<!-- Footer -->
<footer class="py-4 text-center">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> Cit-E Cycling. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
