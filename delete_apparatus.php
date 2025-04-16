<?php
include 'config.php';

$apparatus_id = $_GET['apparatus_id'];

// Start a transaction
$conn->begin_transaction();

try {
    // First, delete all products associated with the apparatus
    $delete_products = $conn->prepare("DELETE FROM products WHERE apparatus_id = ?");
    $delete_products->bind_param("i", $apparatus_id);
    $delete_products->execute();

    // Then, delete the apparatus itself
    $delete_apparatus = $conn->prepare("DELETE FROM apparatus WHERE id = ?");
    $delete_apparatus->bind_param("i", $apparatus_id);
    $delete_apparatus->execute();

    // Commit the transaction
    $conn->commit();

    // Redirect back to the apparatus list
    header("Location: index.php");
    exit();
} catch (Exception $e) {
    // If there is an error, rollback the transaction
    $conn->rollback();
    echo "Error deleting apparatus: " . $e->getMessage();
}
?>
