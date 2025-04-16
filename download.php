<?php
include 'config.php'; 

// Get apparatus ID from the query string
$apparatus_id = isset($_GET['apparatus_id']) ? intval($_GET['apparatus_id']) : 0;

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=inventory_apparatus_' . $apparatus_id . '.csv');

$output = fopen('php://output', 'w');

// Column headers
fputcsv($output, ['ID', 'Name', 'Description', 'Quantity', 'Price', 'Created At', 'Updated At']);

if ($apparatus_id > 0) {
    $stmt = $conn->prepare("SELECT id, name, description, quantity, price, created_at, updated_at FROM products WHERE apparatus_id = ?");
    $stmt->bind_param("i", $apparatus_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
} else {
    // Optional: return all if no apparatus is selected
    $result = $conn->query("SELECT id, name, description, quantity, price, created_at, updated_at FROM products");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, $row);
    }
}

fclose($output);
exit;
?>
