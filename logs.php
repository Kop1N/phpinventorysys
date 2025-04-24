<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';
include 'auth.php'; // Make sure this includes isAdmin() function

// Handle clear logs request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clear_logs']) && isAdmin()) {
    // Confirmation check
    if (isset($_POST['confirm_clear']) && $_POST['confirm_clear'] === 'yes') {
        $conn->query("TRUNCATE TABLE inventory_logs");
        $message = "Logs have been cleared successfully!";
    }
}

// Fetch logs
$result = $conn->query("SELECT * FROM inventory_logs ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Inventory Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-responsive {
            max-height: 70vh;
            overflow-y: auto;
        }
        th {
            position: sticky;
            top: 0;
            background: white;
        }
    </style>
</head>
<body class="container mt-4">
    <h2 class="mb-4">Inventory Logs</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>

    <div class="d-flex justify-content-between mb-3">
        <a href="index.php" class="btn btn-secondary">Back to Dashboard</a>
        
        <?php if (isAdmin()): ?>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#clearLogsModal">
            Clear All Logs
        </button>
        <?php endif; ?>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover table-striped align-middle">
            <thead class="table-dark">
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Product Name</th>
                    <th>Details</th>
                    <th>Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['user_name']) ?></td>
                            <td><?= htmlspecialchars($row['action']) ?></td>
                            <td><?= htmlspecialchars($row['product_name']) ?></td>
                            <td><?= nl2br(htmlspecialchars($row['details'])) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No logs found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Clear Logs Confirmation Modal -->
    <?php if (isAdmin()): ?>
    <div class="modal fade" id="clearLogsModal" tabindex="-1" aria-labelledby="clearLogsModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clearLogsModalLabel">Confirm Clear Logs</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <p>Are you sure you want to clear all logs? This action cannot be undone.</p>
                        <input type="hidden" name="confirm_clear" value="yes">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" name="clear_logs" class="btn btn-danger">Clear All Logs</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>