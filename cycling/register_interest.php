<?php
require_once 'db.php';

$errors   = [];
$success  = false;
$formData = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'city' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitise and validate
    $formData['first_name'] = trim($_POST['first_name'] ?? '');
    $formData['last_name']  = trim($_POST['last_name']  ?? '');
    $formData['email']      = trim($_POST['email']      ?? '');
    $formData['phone']      = trim($_POST['phone']      ?? '');
    $formData['city']       = trim($_POST['city']       ?? '');

    if ($formData['first_name'] === '') {
        $errors['first_name'] = 'First name is required.';
    } elseif (strlen($formData['first_name']) > 100) {
        $errors['first_name'] = 'First name must be 100 characters or fewer.';
    }

    if ($formData['last_name'] === '') {
        $errors['last_name'] = 'Last name is required.';
    } elseif (strlen($formData['last_name']) > 100) {
        $errors['last_name'] = 'Last name must be 100 characters or fewer.';
    }

    if ($formData['email'] === '') {
        $errors['email'] = 'Email address is required.';
    } elseif (!filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address.';
    }

    if ($formData['phone'] === '') {
        $errors['phone'] = 'Phone number is required.';
    } elseif (!preg_match('/^[\d\s\+\-\(\)]{7,20}$/', $formData['phone'])) {
        $errors['phone'] = 'Please enter a valid phone number.';
    }

    if ($formData['city'] === '') {
        $errors['city'] = 'City is required.';
    } elseif (strlen($formData['city']) > 100) {
        $errors['city'] = 'City must be 100 characters or fewer.';
    }

    if (empty($errors)) {
        $conn = getConnection();
        $stmt = $conn->prepare(
            'INSERT INTO interest (first_name, last_name, email, phone, city) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'sssss',
            $formData['first_name'],
            $formData['last_name'],
            $formData['email'],
            $formData['phone'],
            $formData['city']
        );
        if ($stmt->execute()) {
            $success = true;
            $formData = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'city' => ''];
        } else {
            $errors['db'] = 'An error occurred while saving your registration. Please try again.';
        }
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Interest – Cit-E Cycling</title>
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
                <li class="nav-item"><a class="nav-link active" href="register_interest.php">Register Interest</a></li>
                <li class="nav-item"><a class="nav-link" href="search.php">Search</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Admin Login</a></li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <h2 class="fw-bold mb-1"><i class="bi bi-clipboard-check"></i> Register Your Interest</h2>
            <p class="text-muted mb-4">Sign up to hear about upcoming Cit-E Cycling events in your city.</p>

            <?php if ($success): ?>
            <div class="alert alert-success" role="alert">
                <i class="bi bi-check-circle-fill"></i> Thank you! Your interest has been registered. We'll be in touch soon.
            </div>
            <?php endif; ?>

            <?php if (isset($errors['db'])): ?>
            <div class="alert alert-danger" role="alert">
                <i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($errors['db']) ?>
            </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form method="post" action="register_interest.php" novalidate id="registerForm">

                        <div class="row g-3">
                            <!-- First Name -->
                            <div class="col-sm-6">
                                <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['first_name']) ? 'is-invalid' : '' ?>"
                                       id="first_name" name="first_name"
                                       value="<?= htmlspecialchars($formData['first_name']) ?>"
                                       maxlength="100" required>
                                <?php if (isset($errors['first_name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['first_name']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Last Name -->
                            <div class="col-sm-6">
                                <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['last_name']) ? 'is-invalid' : '' ?>"
                                       id="last_name" name="last_name"
                                       value="<?= htmlspecialchars($formData['last_name']) ?>"
                                       maxlength="100" required>
                                <?php if (isset($errors['last_name'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['last_name']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Email -->
                            <div class="col-12">
                                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
                                       id="email" name="email"
                                       value="<?= htmlspecialchars($formData['email']) ?>"
                                       maxlength="150" required>
                                <?php if (isset($errors['email'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['email']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- Phone -->
                            <div class="col-sm-6">
                                <label for="phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control <?= isset($errors['phone']) ? 'is-invalid' : '' ?>"
                                       id="phone" name="phone"
                                       value="<?= htmlspecialchars($formData['phone']) ?>"
                                       maxlength="20" required>
                                <?php if (isset($errors['phone'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['phone']) ?></div>
                                <?php endif; ?>
                            </div>

                            <!-- City -->
                            <div class="col-sm-6">
                                <label for="city" class="form-label fw-semibold">City <span class="text-danger">*</span></label>
                                <input type="text" class="form-control <?= isset($errors['city']) ? 'is-invalid' : '' ?>"
                                       id="city" name="city"
                                       value="<?= htmlspecialchars($formData['city']) ?>"
                                       maxlength="100" required>
                                <?php if (isset($errors['city'])): ?>
                                <div class="invalid-feedback"><?= htmlspecialchars($errors['city']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-send"></i> Submit Registration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="py-4 text-center mt-auto">
    <div class="container">
        <p class="mb-0">&copy; <?= date('Y') ?> Cit-E Cycling. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Client-side Bootstrap validation
(function () {
    'use strict';
    var form = document.getElementById('registerForm');
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
