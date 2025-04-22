<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

// Get apparatus_id from the URL
$apparatus_id = isset($_GET['apparatus_id']) ? $_GET['apparatus_id'] : 0;

// Prepare the query to fetch all products for the specified apparatus
$query = "SELECT * FROM products WHERE apparatus_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $apparatus_id);
$stmt->execute();
$result = $stmt->get_result();

// Check if there are any products
if ($result->num_rows > 0) {
    // Set headers to download as CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="inventory.csv"');
    $output = fopen('php://output', 'w');

    // Add the column headers to the CSV file
    fputcsv($output, ['ID', 'Name', 'Description', 'Shelf/Box #', 'Location', 'Quantity', 'Created', 'Updated', 'Barcode']);

    // Add the product data to the CSV
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['name'],
            $row['description'],
            $row['shelf_box_number'],
            $row['location'],
            $row['quantity'],
            $row['created_at'],
            $row['updated_at'],
            $row['barcode']
        ]);
    }

    // Close the file after writing
    fclose($output);
    exit();
} else {
    // If no products are found, show an error message
    echo "No products found.";
    exit();
}
?>
