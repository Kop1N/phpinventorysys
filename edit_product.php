<?php
session_start();
require_once 'auth.php';
requireLogin();
requireAdmin();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
$apparatus_id = $_GET['apparatus_id'] ?? null;

// Fetch original product data
$product = $conn->query("SELECT * FROM products WHERE id = " . intval($id))->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $shelf_box_number = $_POST['shelf_box_number'];
    $location = $_POST['location'];
    $qty = (int)$_POST['quantity'];
    $serial_number = $_POST['serial_number'];
    $invoice_number = $_POST['invoice_number'];

    // Update the product
    $stmt = $conn->prepare("UPDATE products SET name=?, description=?, shelf_box_number=?, location=?, quantity=?, serial_number=?, invoice_number=? WHERE id=?");
    $stmt->bind_param("ssssissi", $name, $desc, $shelf_box_number, $location, $qty, $serial_number, $invoice_number, $id);
    $stmt->execute();

    // Get user name
    $user_id = $_SESSION['user_id'];
    $user_stmt = $conn->prepare("SELECT name FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user_result = $user_stmt->get_result();
    $user = $user_result->fetch_assoc();
    $user_name = $user['name'];

    // Compare old and new values
    $changes = [];
    if ($product['name'] !== $name) $changes[] = "Name: '{$product['name']}' → '$name'";
    if ($product['description'] !== $desc) $changes[] = "Description: '{$product['description']}' → '$desc'";
    if ($product['shelf_box_number'] !== $shelf_box_number) $changes[] = "Shelf/Box #: '{$product['shelf_box_number']}' → '$shelf_box_number'";
    if ($product['location'] !== $location) $changes[] = "Location: '{$product['location']}' → '$location'";
    if ((int)$product['quantity'] !== $qty) $changes[] = "Quantity: '{$product['quantity']}' → '$qty'";
    if ($product['serial_number'] !== $serial_number) $changes[] = "Serial #: '{$product['serial_number']}' → '$serial_number'";
    if ($product['invoice_number'] !== $invoice_number) $changes[] = "Invoice #: '{$product['invoice_number']}' → '$invoice_number'";

    $action = "Edited";
    $product_name = $name;
    $details = count($changes) ? implode(", ", $changes) : "No changes made";

    // Insert into log
    $log_stmt = $conn->prepare("INSERT INTO inventory_logs (user_name, action, product_name, details) VALUES (?, ?, ?, ?)");
    $log_stmt->bind_param("ssss", $user_name, $action, $product_name, $details);
    $log_stmt->execute();

    header("Location: view_inventory.php?apparatus_id=" . urlencode($apparatus_id));
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h2>Edit Product</h2>
    <form method="POST">
        <label class="form-label">Name:</label>
        <input name="name" class="form-control mb-2" value="<?= htmlspecialchars($product['name']) ?>" required>

        <label class="form-label">Description:</label>
        <textarea name="description" class="form-control mb-2"><?= htmlspecialchars($product['description']) ?></textarea>

        <label class="form-label">Shelf/Box #:</label>
        <input name="shelf_box_number" class="form-control mb-2" value="<?= htmlspecialchars($product['shelf_box_number']) ?>">

        <label class="form-label">Location:</label>
        <input name="location" class="form-control mb-2" value="<?= htmlspecialchars($product['location']) ?>">

        <label class="form-label">Quantity:</label>
        <input name="quantity" type="number" class="form-control mb-3" value="<?= htmlspecialchars($product['quantity']) ?>" required>

        <label class="form-label">Serial Number:</label>
        <input name="serial_number" class="form-control mb-2" value="<?= htmlspecialchars($product['serial_number']) ?>">

        <label class="form-label">Invoice Number:</label>
        <input name="invoice_number" class="form-control mb-2" value="<?= htmlspecialchars($product['invoice_number']) ?>">

        <button class="btn btn-primary">Update</button>
        <a href="view_inventory.php?apparatus_id=<?= urlencode($apparatus_id) ?>" class="btn btn-secondary">Cancel</a>
    </form>
</body>
</html>