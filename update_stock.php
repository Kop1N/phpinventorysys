<?php
include 'config.php';

$id = $_GET['id'];
$action = $_GET['action'];
$apparatus_id = isset($_GET['apparatus_id']) ? intval($_GET['apparatus_id']) : 0;

$product = $conn->query("SELECT quantity FROM products WHERE id=$id")->fetch_assoc();

if ($product) {
    $qty = $product['quantity'];

    if ($action == 'add') {
        $qty += 1;
    } elseif ($action == 'subtract' && $qty > 0) {
        $qty -= 1;
    }

    $stmt = $conn->prepare("UPDATE products SET quantity=? WHERE id=?");
    $stmt->bind_param("ii", $qty, $id);
    $stmt->execute();
}

// Redirect back to the apparatus-specific inventory view
header("Location: view_inventory.php?apparatus_id=" . $apparatus_id);
exit;
?>
