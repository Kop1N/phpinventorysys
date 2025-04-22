<?php
include 'config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Check if passwords match
    if ($password !== $confirm_password) {
        echo "<div class='alert alert-danger mt-3'>Passwords do not match. Please try again.</div>";
    } else {
        // Hash password if they match
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user into the database
        $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed_password);
        if ($stmt->execute()) {
            echo "<div class='alert alert-success mt-3'>Registration successful. <a href='login.php'>Login here</a></div>";
        } else {
            echo "<div class='alert alert-danger mt-3'>Error: " . $stmt->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <style>
        /* Background image with low opacity */
        body {
            background-image: url('resources/frontier.jpg'); /* Change to your image path */
            background-size: cover;
            background-position: center;
            position: relative;
            height: 100vh;
        }

        /* Overlay with low opacity to make content readable */
        .background-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4); /* Black overlay with 40% opacity */
            z-index: -1;
        }

        /* Ensuring content stays on top of the background */
        .container {
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>

<!-- Overlay to darken the background slightly -->
<div class="background-overlay"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">

        <div class="text-center mb-4">
            <img src="resources/images.png" alt="Logo" class="img-fluid" style="max-width: 150px;">
        </div>

        <body class="login-page">
          <div class="container">
           <h2 class="text-center mb-4 login-heading">Register</h2>
          </div>
        </body>


        <!-- Registration Form -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>

            <!-- Confirm Password Field -->
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Register</button>
        </form>

        <!-- Login Link -->
        <p class="mt-3 text-center">Already have an account? <a href="login.php" class="btn btn-link p-0">Login here</a></p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
