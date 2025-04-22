<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include 'config.php'; // Database connection

// Handle search query
$search_term = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM apparatus";
if (!empty($search_term)) {
    $search_term = $conn->real_escape_string($search_term);
    $sql .= " WHERE name LIKE '%$search_term%' OR description LIKE '%$search_term%'";
}

// Retrieve apparatus based on search or all
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">   
    <style>
    .search-container {
        display: flex;
        justify-content: flex-end; /* Push to the right */
        align-items: flex-end; /* Align items to the bottom */
        margin-top: 5px; /* Add some space below */

    }
    .search-container input[type="text"] {
        margin-left: 10px;
    }
</style>
</head>

<body>

    <div class="container-fluid">
    <div class="row">
            <div class="col-md-2 p-2 sidebar">
                <h4> </h4>
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

            <div class="col-md-10">
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h1>FRONTIER'S INVENTORY SYSTEM</h1>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <h2>Apparatus List</h2>
                    <div class="search-container">
                        <form method="GET" action="">
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" placeholder="Search Apparatus" name="search" value="<?= htmlspecialchars($search_term) ?>">
                                <button class="btn btn-outline-secondary btn-sm" type="submit">Search</button>
                            </div>
                        </form>
                    </div>
                </div>

                <table class="table table-bordered mt-2">
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
                                <td><a href="view_inventory.php?apparatus_id=<?= $row['id'] ?>"><?= $row['name'] ?></a></td>
                                <td><?= $row['description'] ?></td>
                                <td>
                                    <a href="delete_apparatus.php?apparatus_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this apparatus and all its inventory?')">Delete Apparatus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                        <?php if ($result->num_rows === 0 && !empty($search_term)): ?>
                            <tr><td colspan="4">No apparatus found matching your search criteria.</td></tr>
                        <?php elseif ($result->num_rows === 0): ?>
                            <tr><td colspan="4">No apparatus available in the inventory.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>