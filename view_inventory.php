<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login page if not logged in
    exit();
}

include 'config.php'; // Database connection

// Get apparatus ID from the URL
$apparatus_id = isset($_GET['apparatus_id']) ? $_GET['apparatus_id'] : 0;

// Retrieve all products related to the selected apparatus
$query = "SELECT * FROM products WHERE apparatus_id = ?";
$counter = 1;
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $apparatus_id);
$stmt->execute();
$result = $stmt->get_result();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Libre+Barcode+128&display=swap" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        .barcode-link {
            cursor: pointer;
            text-decoration: none; /* Remove default link underline */
            color: inherit; /* Inherit text color */
        }
        .barcode-link:hover {
            opacity: 0.8; /* Slightly fade on hover for visual feedback */
        }
        .barcode-image {
            max-width: 150px; /* Adjust as needed */
            height: auto;
            display: block; /* Prevent inline spacing issues */
            margin-top: 5px; /* Add some space above the image */
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
                        <a class="nav-link" href="scan_product.php">Scan Product</a>
                    </li>
                    <li class="nav-item">
                        <a href="logout.php" class="btn btn-danger btn-sm logout-btn">Logout</a>
                    </li>
                </ul>
            </div>

            <div class="col-md-9">
                <h1 class="mt-4">
                    <?php if ($apparatus): ?>
                        <?= $apparatus['name'] ?> Inventory
                    <?php else: ?>
                        Inventory (Apparatus Not Found)
                    <?php endif; ?>
                </h1>

                <a href="add_product.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-primary mb-3">Add Product</a>
                <?php if (isset($message)): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                    <?php if (isset($barcode)): ?>
                        <div class="mb-3">
                            <label class="form-label">Barcode:</label>
                            <div class="d-flex align-items-center gap-3">
                                <code class="fs-5"><?= $barcode ?></code>
                                <button onclick="printBarcode('<?= $barcode ?>')" class="btn btn-sm btn-outline-primary">
                                    Print Barcode
                                </button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
                <a href="download.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-info mb-3 ms-2">Download CSV File</a>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Quantity</th>
                            <th>Time Created</th>
                            <th>Time Updated</th>
                            <th>Barcode</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $counter++ ?></td>
                                <td><?= $row['name'] ?></td>
                                <td><?= $row['description'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['created_at'] ?></td>
                                <td><?= $row['updated_at'] ?></td>
                                <td class="text-center">
                                    <?php if ($row['barcode']): ?>
                                        <div class="d-flex flex-column align-items-center">
                                            <a href="#" class="barcode-link" onclick="downloadBarcodeFromTable('<?= $row['barcode'] ?>')">
                                                <?= $row['barcode'] ?>
                                            </a>
                                            <img src="barcodes/<?= $row['barcode'] ?>.png" alt="Barcode" class="barcode-image">
                                        </div>
                                    <?php else: ?>
                                        <?= $row['barcode'] ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_product.php?id=<?= $row['id'] ?>&apparatus_id=<?= $apparatus_id ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="delete.php?id=<?= $row['id'] ?>&apparatus_id=<?= $apparatus_id ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this product?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>

            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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

        function downloadBarcodeFromTable(barcode) {
            if (!barcode) {
                alert('No barcode available to download');
                return;
            }

            // Create a temporary SVG element
            const tempDiv = document.createElement('div');
            tempDiv.style.display = 'none'; // Hide the temporary div
            document.body.appendChild(tempDiv);
            tempDiv.innerHTML = `<svg id="temp-barcode"></svg>`;
            const svg = document.getElementById('temp-barcode');

            // Generate barcode in the SVG element
            JsBarcode(svg, barcode, {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 100,
                displayValue: true,
                fontSize: 16
            });

            // Convert SVG to PNG and trigger download
            const svgData = new XMLSerializer().serializeToString(svg);
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = function() {
                canvas.width = img.width;
                canvas.height = img.height;
                ctx.drawImage(img, 0, 0);

                const pngFile = canvas.toDataURL('image/png');
                const downloadLink = document.createElement('a');
                downloadLink.href = pngFile;
                downloadLink.download = `barcode_${barcode}.png`;
                document.body.appendChild(downloadLink);
                downloadLink.click();
                document.body.removeChild(downloadLink);
                document.body.removeChild(tempDiv); // Clean up the temporary div
            };

            img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
        }
    </script>
</body>
</html>