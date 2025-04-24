<?php
session_start();
require_once 'config.php';
require_once 'auth.php';

requireLogin();
requireAdmin();

// Handle user deletion
if (isset($_GET['delete_user'])) {
    $user_id = intval($_GET['delete_user']);
    if ($user_id != $_SESSION['user_id']) { // Prevent self-deletion
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $_SESSION['message'] = "User deleted successfully";
        header("Location: manage_users.php");
        exit();
    } else {
        $_SESSION['error'] = "You cannot delete yourself";
    }
}

// Fetch all users
$users = $conn->query("SELECT id, username, name, role, created_at FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<body>
    <div class="container mt-3">
        <a href="index.php" class="btn btn-secondary">← Back to Inventory</a>
    </div>



    <div class="container mt-4">
        <h2>User Management</h2>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['error'] ?></div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="mb-3">
            <a href="register.php" class="btn btn-primary">Add New User</a>
        </div>

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $users->fetch_assoc()): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><?= htmlspecialchars($user['username']) ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= $user['role'] ?></td>
                    <td><?= $user['created_at'] ?></td>
                    <td>
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="manage_users.php?delete_user=<?= $user['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this user?')">Delete</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>