<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'config.php';

$apparatus_id = isset($_GET['apparatus_id']) ? $_GET['apparatus_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $barcode = $_POST['barcode'];
    
    // Look up product by barcode
    $stmt = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Product exists - add to inventory
        $product = $result->fetch_assoc();
        $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity + 1 WHERE id = ?");
        $update_stmt->bind_param("i", $product['id']);
        $update_stmt->execute();
        $message = "Product '{$product['name']}' quantity increased!";
    } else {
        $message = "Product not found. Please add it first.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan Product</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <!-- Include barcode scanner library -->
    <script src="https://cdn.jsdelivr.net/npm/quagga/dist/quagga.min.js"></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar same as other pages -->
            
            <div class="col-md-9">
                <h1 class="mt-4">Scan Product</h1>
                
                <?php if (isset($message)): ?>
                    <div class="alert alert-info"><?= $message ?></div>
                <?php endif; ?>
                
                <div class="row">
                    <div class="col-md-6">
                        <form method="POST" action="scan_product.php?apparatus_id=<?= $apparatus_id ?>">
                            <div class="mb-3">
                                <label for="barcode" class="form-label">Barcode:</label>
                                <input type="text" class="form-control" name="barcode" id="barcode" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div id="scanner-container" style="width: 100%; height: 300px; border: 2px solid #ddd;">
                            <p class="text-center mt-5">Camera feed will appear here when scanning</p>
                        </div>
                        <button id="start-scanner" class="btn btn-success mt-2">Start Scanner</button>
                        <button id="stop-scanner" class="btn btn-danger mt-2" disabled>Stop Scanner</button>
                    </div>
                </div>
                
                <div class="mt-4">
                    <a href="view_inventory.php?apparatus_id=<?= $apparatus_id ?>" class="btn btn-secondary">Back to Inventory</a>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Barcode scanner functionality
    document.getElementById('start-scanner').addEventListener('click', function() {
        Quagga.init({
            inputStream: {
                name: "Live",
                type: "LiveStream",
                target: document.querySelector('#scanner-container'),
                constraints: {
                    width: 480,
                    height: 320,
                    facingMode: "environment"
                },
            },
            decoder: {
                readers: ["ean_reader", "ean_8_reader", "code_128_reader"]
            },
        }, function(err) {
            if (err) {
                console.error(err);
                alert("Error initializing scanner: " + err);
                return;
            }
            console.log("Initialization finished. Ready to start");
            Quagga.start();
            
            document.getElementById('start-scanner').disabled = true;
            document.getElementById('stop-scanner').disabled = false;
        });

        Quagga.onDetected(function(result) {
            const code = result.codeResult.code;
            document.getElementById('barcode').value = code;
            Quagga.stop();
            document.getElementById('start-scanner').disabled = false;
            document.getElementById('stop-scanner').disabled = true;
        });
    });

    document.getElementById('stop-scanner').addEventListener('click', function() {
        Quagga.stop();
        document.getElementById('start-scanner').disabled = false;
        document.getElementById('stop-scanner').disabled = true;
    });
    </script>
</body>
</html>