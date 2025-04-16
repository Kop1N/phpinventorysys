<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include 'config.php'; // Database connection

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Insert apparatus into the database
    $stmt = $conn->prepare("INSERT INTO apparatus (name, description) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $description);
    if ($stmt->execute()) {
        $message = "Apparatus added successfully!";
    } else {
        $message = "Error adding apparatus!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Apparatus</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="container mt-4">

    <h1>Add Apparatus</h1>

    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" action="add_apparatus.php">
        <div class="mb-3">
            <label for="name" class="form-label">Apparatus Name:</label>
            <input type="text" class="form-control" name="name" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">Description:</label>
            <textarea class="form-control" name="description" required></textarea>
        </div>

        <button type="submit" class="btn btn-success">Add Apparatus</button>
        <a href="index.php" class="btn btn-secondary">Back to Inventory</a>
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
