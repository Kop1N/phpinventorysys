<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Database connection

// Get apparatus ID from the URL
$apparatus_id = isset($_GET['apparatus_id']) ? $_GET['apparatus_id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];


    // Insert product into the database
    $stmt = $conn->prepare("INSERT INTO products (name, description, quantity, apparatus_id) VALUES (?, ?, ?,?)");
    $stmt->bind_param("ssii", $name, $description, $quantity, $apparatus_id);
    if ($stmt->execute()) {
        $message = "Product added successfully!";
    } else {
        $message = "Error adding product!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
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
            <!-- Sidebar -->

            <!-- Main Content -->
            <div class="col-md-9">
                <h1 class="mt-4">Add Product to Apparatus</h1>

                <?php if (isset($message)): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>

                <form method="POST" action="add_product.php?apparatus_id=<?= $apparatus_id ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name:</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea class="form-control" name="description" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>

                    <button type="submit" class="btn btn-success">Add Product</button>
                    <a href="view_inventory.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-secondary">Back to Inventory</a>
                </form>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
