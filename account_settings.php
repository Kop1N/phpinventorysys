<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$success = $error = "";

// Password change logic
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $currentPassword = isset($_POST['current_password']) ? $_POST['current_password'] : '';
    $newPassword = isset($_POST['new_password']) ? $_POST['new_password'] : '';
    $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $newName = isset($_POST['new_name']) ? $_POST['new_name'] : '';
    $newUsername = isset($_POST['new_username']) ? $_POST['new_username'] : '';

    // Fetch current password hash
    if ($currentPassword) {
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($hash);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($currentPassword, $hash)) {
            $error = "Current password is incorrect.";
        } elseif ($newPassword !== $confirmPassword) {
            $error = "New passwords do not match.";
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $newHash, $userId);
            $stmt->execute();
            $stmt->close();
            $success = "Password changed successfully.";
        }
    }

    // Update name logic
    if ($newName && !$error) {
        $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
        $stmt->bind_param("si", $newName, $userId);
        $stmt->execute();
        $stmt->close();
        $success = "Name updated successfully.";
    }

    // Update username logic
    if ($newUsername && !$error) {
        $stmt = $conn->prepare("UPDATE users SET username = ? WHERE id = ?");
        $stmt->bind_param("si", $newUsername, $userId);
        $stmt->execute();
        $stmt->close();
        $success = "Username updated successfully.";
    }
}

// Fetch user info
$stmt = $conn->prepare("SELECT name, username, role FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$stmt->bind_result($name, $username, $role);
$stmt->fetch();
$stmt->close();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Account Settings</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #2f2f2f;
            font-family: 'Arial', sans-serif;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .alert {
            margin-bottom: 20px;
        }
        h2 {
            color: #003366;
        }
        h4 {
            color: #003366;
        }
        .form-control {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-control:focus {
            border-color: #003366;
            box-shadow: 0 0 5px rgba(0, 51, 102, 0.5);
        }
        .btn-primary {
            background-color: #003366;
            border-color: #003366;
            border-radius: 5px;
        }
        .btn-primary:hover {
            background-color: #00509e;
            border-color: #00509e;
        }
        .btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            border-radius: 5px;
        }
        .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #545b62;
        }
        .card-header {
            background-color: #003366;
            color: white;
        }
        .card-body {
            background-color: #e9ecef;
        }
        .text-darkblue {
            color: #003366;
        }
        .form-section {
            display: none;
            margin-top: 20px;
        }
        .back-to-inventory-btn {
            position: absolute;
            top: 10px;
            right: 10px;
        }
    </style>
</head>
<body>
<div class="container mt-5">
    <h2>User Account Settings</h2>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php elseif ($error): ?>
        <div class="alert alert-danger"><?= $error ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-header">
            <strong>Account Information</strong>
        </div>
        <div class="card-body">
            <p><strong class="text-darkblue">Username:</strong> <?= htmlspecialchars($username) ?> <button class="btn btn-secondary" id="editUsernameBtn">Edit Username</button></p>
            <p><strong class="text-darkblue">Name:</strong> <?= htmlspecialchars($name) ?> 
            <p><strong class="text-darkblue">Role:</strong> <?= htmlspecialchars(ucfirst($role)) ?></p>
        </div>
    </div>

 

    <!-- Edit Username Section (Initially Hidden) -->
    <div id="editUsernameSection" class="form-section">
        <h4>Edit Username</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="new_username" class="form-label">New Username</label>
                <input type="text" name="new_username" id="new_username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
            </div>
            <button class="btn btn-primary" type="submit">Save Username</button>
        </form>
    </div>

    <!-- Change Password Section (Initially Hidden) -->
    <button class="btn btn-primary mt-3" id="changePasswordBtn">Change Password</button>
    <div id="changePasswordSection" class="form-section mt-3">
        <h4>Change Password</h4>
        <form method="POST">
            <div class="mb-3">
                <label for="current_password" class="form-label">Current Password</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">New Password</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Confirm New Password</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button class="btn btn-primary" type="submit">Change Password</button>
        </form>
    </div>
</div>

<!-- Back to Inventory Button -->
<div class="back-to-inventory-btn">
    <a href="index.php" class="btn btn-secondary">Back to Inventory</a>
</div>

<script>
    // Toggle Edit Name Section
    document.getElementById('editNameBtn').addEventListener('click', function() {
        document.getElementById('editNameSection').style.display = 'block';
        // Close Edit Username Section if open
        document.getElementById('editUsernameSection').style.display = 'none';
    });

    // Toggle Edit Username Section
    document.getElementById('editUsernameBtn').addEventListener('click', function() {
        document.getElementById('editUsernameSection').style.display = 'block';
        // Close Edit Name Section if open
        document.getElementById('editNameSection').style.display = 'none';
    });

    // Toggle Change Password Section
    document.getElementById('changePasswordBtn').addEventListener('click', function() {
        document.getElementById('changePasswordSection').style.display = 'block';
    });
</script>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>

