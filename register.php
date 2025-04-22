<?php
include 'config.php'; // Database connection

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash password

    // Insert new user into the database
    $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password);
    if ($stmt->execute()) {
        $registration_success = "Registration successful. <a href='login.php'>Login here</a>";
    } else {
        $registration_error = "Error: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa; /* Light gray background */
            min-height: 100vh; /* Ensure full viewport height */
            display: flex;
            align-items: center; /* Vertically center content */
            justify-content: center; /* Horizontally center content */
            margin: 0; /* Remove default body margin */
        }
        .register-container {
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        .form-label {
            font-weight: bold;
        }
        .btn-primary {
            width: 100%;
        }
        .mt-3 a {
            display: block;
            width: 100%;
            text-align: center;
            margin-top: 10px;
        }
        .alert {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <div class="register-container">
        <h2 class="text-center mb-4">Register</h2>

        <?php if (isset($registration_success)): ?>
            <div class="alert alert-success" role="alert">
                <?php echo $registration_success; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($registration_error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $registration_error; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <input type="text" name="username" class="form-control" id="username" required>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" name="password" class="form-control" id="password" required>
            </div>

            <button type="submit" class="btn btn-primary">Register</button>
        </form>

        <p class="mt-3 text-center">Already have an account? <a href="login.php" class="btn btn-secondary btn-sm">Login here</a></p>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>