<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$apparatus_id = isset($_GET['apparatus_id']) ? $_GET['apparatus_id'] : 0;

// --- Handle Search ---
$search_term = '';
$search_submitted = isset($_GET['search']);
$search_query = '';
$params = [];
$types = 'i';
$params[] = $apparatus_id;

if ($search_submitted) {
    $search_term = $_GET['search'];
    $search_query = "AND (name LIKE ? OR description LIKE ? OR barcode LIKE ?)";
    $types .= 'sss';
    $like_term = '%' . $search_term . '%';
    $params[] = $like_term;
    $params[] = $like_term;
    $params[] = $like_term;
}

// --- Retrieve Products ---
$query = "SELECT * FROM products WHERE apparatus_id = ? $search_query";
$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$product_count = $result->num_rows;

// --- Retrieve Apparatus Info ---
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
    <title>Manage Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-2 p-2 sidebar">
            <div class="sidebar-logo text-center mb-4">
                <img src="resources/images.png" alt="Logo" class="img-fluid" style="max-width: 120px;">
            </div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">View Apparatus</a>
                </li>
                <li class="nav-item">
                    <a href="scan_product.php?apparatus_id=<?= $apparatus_id ?>" class="nav-link">Scan Product</a>
                </li>
                <li class="nav-item">
                    <a href="logout.php" class="btn btn-danger btn-sm logout-btn">Logout</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <h1 class="mt-4"><?= $apparatus['name'] ?> Inventory</h1>

            <!-- Top Controls -->
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <div class="d-flex gap-2">
                    <a href="add_product.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-primary">Add Product</a>
                    <a href="download.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-info">Download CSV</a>
                </div>

                <!-- Search Form -->
                <form method="GET" action="view_inventory.php" class="d-flex" style="max-width: 300px;">
                    <input type="hidden" name="apparatus_id" value="<?= $apparatus_id ?>">
                    <input type="text" class="form-control me-2" name="search" value="<?= htmlspecialchars($search_term) ?>" placeholder="Search...">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </form>
            </div>

            <!-- Search Message -->
            <?php if ($search_submitted && $product_count == 0): ?>
                <div class="alert alert-warning">No products found for "<strong><?= htmlspecialchars($search_term) ?></strong>".</div>
            <?php endif; ?>

            <!-- Product Table -->
            <?php if ($product_count > 0 || $search_submitted): ?>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Shelves/Box #</th>
                            <th>Location</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Created</th>
                            <th>Updated</th>
                            <th>Barcode</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $counter = 1; while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= htmlspecialchars($row['shelf_box_number']) ?></td>
                                <td><?= htmlspecialchars($row['location']) ?></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['created_at'] ?></td>
                                <td><?= $row['updated_at'] ?></td>
                                <td>
                                    <?= $row['barcode'] ?>
                                    
                                </td>
                                <td>
                                    <a href="edit_product.php?id=<?= $row['id'] ?>&apparatus_id=<?= $apparatus_id ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete.php?id=<?= $row['id'] ?>&apparatus_id=<?= $apparatus_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Barcode Print Script -->
<script>
function printBarcode(barcode) {
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Barcode Print</title>
            <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
            <style>
                body { text-align: center; padding: 20px; }
                .barcode {
                    font-family: 'Libre Barcode 128', cursive;
                    font-size: 72px;
                    margin: 20px 0;
                }
                .barcode-text {
                    font-family: Arial, sans-serif;
                    font-size: 16px;
                    letter-spacing: 5px;
                }
            </style>
        </head>
        <body>
            <div class="barcode">${barcode}</div>
            <div class="barcode-text">${barcode}</div>
            <script>window.print();</script>
        </body>
        </html>
    `);
    printWindow.document.close();
}
</script>

</body>
</html>
