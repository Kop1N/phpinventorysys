<?php
session_start();
include 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Fetch the user from the database
    $stmt = $conn->prepare("SELECT id, username, password, role, name FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        // Password is correct, start a session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role']; // Store user role in session
        $_SESSION['name'] = $user['name']; // Store user's name in session
        
        header("Location: index.php");
        exit();
    } else {
        echo "<div class='alert alert-danger mt-3'>Invalid username or password.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body style="background-color: #f4f7fc;">

<!-- Background Image with Low Opacity -->
<div class="background-image"></div>

<div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="card shadow-sm p-4" style="max-width: 400px; width: 100%;">
        <!-- Logo -->
        <div class="text-center mb-4">
            <img src="resources/images.png" alt="Logo" class="img-fluid" style="max-width: 150px;">
        </div>

        <body class="login-page">
          <div class="container">
           <h2 class="text-center mb-4 login-heading">Login</h2>
          </div>
        </body>


        <!-- Login Form -->
        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2">Login</button>
        </form>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>

    .background-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('resources/frontier.jpg'); 
        background-size: cover;
        background-position: center;
        opacity: 0.3; 
        z-index: -1; 
    }

  
    .container {
        position: relative;
        z-index: 1;
    }
</style>

</body>
</html>
