<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include 'config.php'; // Database connection

// Retrieve all apparatus from the database
$result = $conn->query("SELECT * FROM apparatus");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <!-- Link to the external CSS file -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">  <!-- External style -->
</head>

<body>

    <div class="container-fluid">
    <div class="row">
            <!-- Sidebar -->
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

            <!-- Main Content -->
            <div class="col-md-9">
                <h1 class="mt-4">FRONTIER'S INVENTORY SYSTEM</h1>

                <!-- Apparatus Table -->
                <h2>Apparatus List</h2>
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
                                <td><a href="view_inventory.php?apparatus_id=<?= $row['id'] ?>"><?= $row['name'] ?></a></td>
                                <td><?= $row['description'] ?></td>
                                <td>
                                <a href="delete_apparatus.php?apparatus_id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this apparatus and all its inventory?')">Delete Apparatus</a>


                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
