<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include 'config.php'; // Database connection

// Get apparatus ID from the URL
$apparatus_id = isset($_GET['apparatus_id']) ? $_GET['apparatus_id'] : 0;

// Retrieve all products related to the selected apparatus
$query = "SELECT * FROM products WHERE apparatus_id = ?";
$counter = 1;
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $apparatus_id);
$stmt->execute();
$result = $stmt->get_result();
$apparatus_query = "SELECT * FROM apparatus WHERE id = ?";
$stmt_apparatus = $conn->prepare($apparatus_query);
$stmt_apparatus->bind_param("i", $apparatus_id);
$stmt_apparatus->execute();
$apparatus_result = $stmt_apparatus->get_result();
$apparatus = $apparatus_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-2 sidebar">
                <h4> </h4>
                <div class="sidebar-logo text-center mb-4">
                    <img src="resources/images.png" alt="Logo" class="img-fluid" style="max-width: 120px;">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">View Apparatus</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="add_apparatus.php">Add Apparatus</a>
                    </li>
                    <li class="nav-item">
                    <a href="logout.php" class="btn btn-danger btn-sm logout-btn">Logout</a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <h1 class="mt-4"><?= $apparatus['name'] ?> Inventory</h1>

                <a href="add_product.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-primary mb-3">Add Product</a>
                <a href="download.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-info mb-3 ms-2">Download CSV File</a>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Time Created</th>
                            <th>Time Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['description'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['created_at'] ?></td> <!-- Time Created -->
                                <td><?= $row['updated_at'] ?></td> <!-- Time Updated -->
                                <td>
                                <a href="edit_product.php?id=<?= $row['id'] ?>&apparatus_id=<?= $apparatus_id ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delete.php?id=<?= $row['id'] ?>&apparatus_id=<?= $apparatus_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
                                <a href="update_stock.php?id=<?= $row['id'] ?>&action=add&apparatus_id=<?= $apparatus_id ?>" class="btn btn-outline-success btn-sm">+</a>
                                <a href="update_stock.php?id=<?= $row['id'] ?>&action=subtract&apparatus_id=<?= $apparatus_id ?>" class="btn btn-outline-danger btn-sm">−</a>

                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
