<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

// Only allow admins to access this page directly
if ($_SERVER['REQUEST_METHOD'] != 'POST' && !isAdmin()) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    $role = isset($_POST['role']) && isAdmin() ? $_POST['role'] : 'user'; // Default to user if not admin
    
    // Validate input
    // ... (your existing validation code)
    
    // Hash password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $conn->prepare("INSERT INTO users (username, password, name, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $username, $hashed_password, $name, $role);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "User registered successfully";
        header("Location: " . (isAdmin() ? "manage_users.php" : "login.php"));
        exit();
    } else {
        $error = "Registration failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Register New User</h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required>
            </div>
            
            <?php if (isAdmin()): ?>
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn btn-primary">Register</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>