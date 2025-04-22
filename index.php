<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

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

        <!-- Main Content -->
        <div class="col-md-9">
            <h1 class="mt-4">FRONTIER'S INVENTORY SYSTEM</h1>

            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h2>Apparatus List</h2>

                <!-- Search Form -->
                <form method="GET" action="index.php" class="d-flex" style="max-width: 300px;">
                    <input type="text" class="form-control me-2" name="search" value="<?= htmlspecialchars($search_term) ?>" placeholder="Search apparatus...">
                    <button class="btn btn-outline-secondary" type="submit">Search</button>
                </form>
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
                                    <a href="delete_apparatus.php?apparatus_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this apparatus and all its inventory?')">Delete Apparatus</a>
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
