<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
include 'auth.php';

// Handle CSV download
if (isset($_GET['download_all']) && $_GET['download_all'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="all_inventory_'.date('Y-m-d').'.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Write CSV headers
    fputcsv($output, [
        'Apparatus ID',
        'Apparatus Name',
        'Serial Number',
        'Product Name',
        'Shelves/Box #',
        'Location',
        'Description',
        'Quantity',
        'Invoice Number',
        'Created At',
        'Updated At',
        'Barcode'
    ]);
    
    // Get all apparatus with their products
    $apparatus_query = "SELECT a.id AS apparatus_id, a.name AS apparatus_name, 
                        p.* FROM apparatus a
                        LEFT JOIN products p ON a.id = p.apparatus_id
                        ORDER BY a.name, p.name";
    $result = $conn->query($apparatus_query);
    
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['apparatus_id'],
            $row['apparatus_name'],
            $row['serial_number'],
            $row['name'],
            $row['shelf_box_number'],
            $row['location'],
            $row['description'],
            $row['quantity'],
            $row['invoice_number'],
            $row['created_at'],
            $row['updated_at'],
            $row['barcode']
        ]);
    }
    
    fclose($output);
    exit();
}

// --- Handle Search ---
$search_term = '';
$search_submitted = isset($_GET['search']);
$query = "SELECT * FROM apparatus";
$params = [];
$types = '';
$where = '';

if ($search_submitted && !empty($_GET['search'])) {
    $search_term = $_GET['search'];
    $where = " WHERE name LIKE ? OR description LIKE ?";
    $like_term = '%' . $search_term . '%';
    $params = [$like_term, $like_term];
    $types = 'ss';
    $query .= $where;
}

// --- Prepare and Execute Query ---
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
$apparatus_count = $result->num_rows;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inventory Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="add_apparatus.php">Add Apparatus</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                    <a class="nav-link" href="account_settings.php">User Account Settings</a>
                </li>
                <?php if (isAdmin()): ?>
                <li class="nav-item">
                  <a class="nav-link" href="manage_users.php">Manage Users</a>
                </li>
                <?php endif; ?>
                <li class="nav-item">
                  <a href="logs.php" class="nav-link">View Logs</a>
                 </li>
                <li class="nav-item mt-2">
                <a href="logout.php" class="btn btn-danger btn-sm logout-btn">Logout</a>
                </li>
                <li class="nav-item">
                 <span class="nav-link" style="color:rgb(141, 51, 51);">Logged in as: <?= htmlspecialchars(currentUserName()) ?> (<?= currentUserRole() ?>)</span>
                </li>
            </ul>

        </div>

        <!-- Main Content -->
        <div class="col-md-9">
            <h1 class="mt-4">FRONTIER'S INVENTORY SYSTEM</h1>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2>Apparatus List</h2>

                <div class="d-flex gap-2">
                    <!-- Download All CSV Button -->
                    <a href="index.php?download_all=csv" class="btn btn-info">Download Full Inventory CSV</a>
                    <!-- Search Form -->
                    <form method="GET" action="index.php" class="d-flex" style="max-width: 300px;">
                        <input type="text" class="form-control me-2" name="search" value="<?= htmlspecialchars($search_term) ?>" placeholder="Search apparatus...">
                        <button class="btn btn-outline-secondary" type="submit">Search</button>
                    </form>
                    
                    
                </div>
            </div>

            <!-- Search Message -->
            <?php if ($search_submitted && $apparatus_count == 0): ?>
                <div class="alert alert-warning">No apparatus found for "<strong><?= htmlspecialchars($search_term) ?></strong>".</div>
            <?php endif; ?>

            <!-- Apparatus Table -->
            <?php if ($apparatus_count > 0 || $search_submitted): ?>
                <table class="table table-bordered mt-4">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $row['id'] ?></td>
                                <td><a href="view_inventory.php?apparatus_id=<?= $row['id'] ?>"><?= htmlspecialchars($row['name']) ?></a></td>
                                <td><?= htmlspecialchars($row['description']) ?></td>
                                <td>
                                    <?php if (isAdmin()): ?>
                                    <a href="delete_apparatus.php?apparatus_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this apparatus and all its inventory?')">Delete Apparatus</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>