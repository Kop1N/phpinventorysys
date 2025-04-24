<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

requireLogin();
requireAdmin();

$user_id = intval($_GET['id'] ?? 0);

// Fetch user data
$stmt = $conn->prepare("SELECT id, username, name, role FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    $_SESSION['error'] = "User not found";
    header("Location: manage_users.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $role = $_POST['role'];
    $password = $_POST['password'];
    
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, role = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $role, $hashed_password, $user_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, role = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $role, $user_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "User updated successfully";
        header("Location: manage_users.php");
        exit();
    } else {
        $error = "Update failed: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>Edit User: <?= htmlspecialchars($user['username']) ?></h2>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" readonly>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-control">
                    <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            
            <div class="mb-3">
                <label class="form-label">New Password (leave blank to keep current)</label>
                <input type="password" name="password" class="form-control">
            </div>
            
            <button type="submit" class="btn btn-primary">Update User</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</body>
</html>