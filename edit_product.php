<?php
include 'config.php';

// Get product ID and apparatus ID (for redirection after update)
$id = $_GET['id'];
$apparatus_id = isset($_GET['apparatus_id']) ? $_GET['apparatus_id'] : null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $shelf_box_number = $_POST['shelf_box_number'];
    $location = $_POST['location'];
    $qty = $_POST['quantity'];
    
    // Prepare and execute the update statement
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, shelf_box_number=?, location=?, quantity=? WHERE id=?");
    $stmt->bind_param("ssssii", $name, $desc, $shelf_box_number, $location, $qty, $id);    
    $stmt->execute();
    
    // Redirect back to the apparatus view after update
    if ($apparatus_id) {
        header("Location: view_inventory.php?apparatus_id=$apparatus_id");
    } else {
        header("Location: index.php");
    }
    exit(); // Always exit after header redirection
}

// Get the current product details
$product = $conn->query("SELECT * FROM products WHERE id=$id")->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Edit Product</h2>
    <form method="POST">
        <label for="quantity" class="form-label">Name:</label>
        <input name="name" class="form-control mb-2" value="<?= $product['name'] ?>" required>
        <label for="quantity" class="form-label">Description:</label>
        <textarea name="description" class="form-control mb-2"><?= $product['description'] ?></textarea>
        <label for="quantity" class="form-label">Shelf/Box#:</label>
        <textarea name="shelf_box_number" class="form-control mb-2"><?= $product['shelf_box_number'] ?></textarea>
        <label for="quantity" class="form-label">Location:</label>
        <textarea name="location" class="form-control mb-2"><?= $product['location'] ?></textarea>
        <label for="quantity" class="form-label">Quantity:</label>
        <input name="quantity" type="number" class="form-control mb-2" value="<?= $product['quantity'] ?>" required>
        <button class="btn btn-primary">Update</button>
        <a href="view_inventory.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>
