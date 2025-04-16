<?php
include 'config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$apparatus_id = isset($_GET['apparatus_id']) ? intval($_GET['apparatus_id']) : 0;

// Delete the product
$conn->query("DELETE FROM products WHERE id=$id");

// Redirect back to the current apparatus view
header("Location: view_inventory.php?apparatus_id=" . $apparatus_id);
exit;
?>
