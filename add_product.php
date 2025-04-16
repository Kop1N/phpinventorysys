<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php'; // Database connection

// Get apparatus ID from the URL
$apparatus_id = isset($_GET['apparatus_id']) ? $_GET['apparatus_id'] : 0;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Get form data
    $name = $_POST['name'];
    $description = $_POST['description'];
    $quantity = $_POST['quantity'];
    
    // Generate a unique barcode (EAN-13 format example)
    $barcode = '20' . str_pad(mt_rand(0, 999999999), 10, '0', STR_PAD_LEFT);
    
    // Check if barcode exists (very unlikely but possible)
    $check_stmt = $conn->prepare("SELECT id FROM products WHERE barcode = ?");
    $check_stmt->bind_param("s", $barcode);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        // If by chance barcode exists, generate a new one
        $barcode = '20' . str_pad(mt_rand(0, 999999999), 10, '0', STR_PAD_LEFT);
    }

    // Insert product into the database with barcode
    $stmt = $conn->prepare("INSERT INTO products (name, description, quantity, apparatus_id, barcode) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssiis", $name, $description, $quantity, $apparatus_id, $barcode);
    
    if ($stmt->execute()) {
        $message = "Product added successfully!";
        $_SESSION['current_barcode'] = $barcode; // Store for download
    } else {
        $message = "Error adding product!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.0/font/bootstrap-icons.css">
    <style>
        .barcode-container {
            display: none; /* Hide the visual barcode */
        }
    </style>
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
                        <a class="nav-link" href="scan_product.php">Scan Product</a>
                    </li>
                    <li class="nav-item">
                    <a href="logout.php" class="btn btn-danger btn-sm logout-btn">Logout</a>
                    </li>
                </ul>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <h1 class="mt-4">Add Product to Apparatus</h1>

                <?php if (isset($message)): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                    <?php if (isset($_SESSION['current_barcode'])): ?>
                        <div class="mb-3">
                            <label class="form-label">Barcode Number:</label>
                            <div class="d-flex align-items-center gap-3">
                                <code class="fs-5"><?= $_SESSION['current_barcode'] ?></code>
                                <button onclick="downloadBarcode()" class="btn btn-success">
                                    <i class="bi bi-download"></i> Download Barcode
                                </button>
                            </div>
                            <!-- Hidden SVG for barcode generation -->
                            <svg id="barcode-svg" class="barcode-container"></svg>
                        </div>
                        <?php unset($_SESSION['current_barcode']); ?>
                    <?php endif; ?>
                <?php endif; ?>

                <form method="POST" action="add_product.php?apparatus_id=<?= $apparatus_id ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label">Product Name:</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description:</label>
                        <textarea class="form-control" name="description" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity:</label>
                        <input type="number" class="form-control" name="quantity" required min="1" value="1">
                    </div>

                    <button type="submit" class="btn btn-success">Add Product</button>
                    <a href="view_inventory.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-secondary">Back to Inventory</a>
                    <a href="scan_product.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-primary">
                        <i class="bi bi-upc-scan"></i> Scan Product
                    </a>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    // Generate hidden barcode when page loads
    document.addEventListener('DOMContentLoaded', function() {
        <?php if (isset($_SESSION['current_barcode'])): ?>
            JsBarcode('#barcode-svg', '<?= $_SESSION['current_barcode'] ?>', {
                format: "CODE128",
                lineColor: "#000",
                width: 2,
                height: 100,
                displayValue: false
            });
        <?php endif; ?>
    });

    function downloadBarcode() {
    const barcode = document.querySelector('code.fs-5')?.textContent;
    if (!barcode) {
        alert('No barcode available to download');
        return;
    }
    
    // Create a temporary SVG element
    const tempDiv = document.createElement('div');
    document.body.appendChild(tempDiv);
    tempDiv.innerHTML = `<svg id="temp-barcode"></svg>`;
    
    // Generate barcode in memory
    JsBarcode('#temp-barcode', barcode, {
        format: "CODE128",
        lineColor: "#000",
        width: 2,
        height: 100,
        displayValue: true,
        fontSize: 16
    });
    
    // Convert SVG to PNG and download
    const svg = document.getElementById('temp-barcode');
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
        document.body.removeChild(tempDiv);
    };
    
    img.src = 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svgData)));
}
    </script>
</body>
</html>