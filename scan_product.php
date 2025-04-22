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
    $quantity = isset($_POST['quantity']) ? $_POST['quantity'] : 1; // Default to 1 if no quantity provided

    // Look up product by barcode
    $stmt = $conn->prepare("SELECT * FROM products WHERE barcode = ?");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Product exists - add the specified quantity to inventory
        $product = $result->fetch_assoc();
        $update_stmt = $conn->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $update_stmt->bind_param("ii", $quantity, $product['id']);
        $update_stmt->execute();
        $message = "Product '{$product['name']}' quantity increased by {$quantity}!";
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
    <script src="https://cdn.jsdelivr.net/npm/quagga/dist/quagga.min.js"></script>
    <style>
        /* Style for the scanner container and video feed */
        #scanner-container {
            width: 100%;
            height: 300px;
            border: 2px solid #ddd;
            position: relative; /* Needed for absolute positioning of video */
            overflow: hidden; /* Clip any overflow from the video */
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #f8f9fa; /* Optional background color */
        }

        #scanner-container video {
            position: absolute;
            top: 0;
            left: 0;
            width: 100% !important; /* Important to override Quagga's inline styles */
            height: 100% !important; /* Important to override Quagga's inline styles */
            object-fit: cover; /* Ensure video covers the container without distortion */
        }

        #scanner-container p.text-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10; /* Ensure text is on top of the video before it starts */
            color: #6c757d;
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
                            <div class="mb-3">
                                <label for="quantity" class="form-label">Quantity:</label>
                                <input type="number" class="form-control" name="quantity" id="quantity" min="1" value="1" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Add Product</button>
                        </form>
                    </div>
                    <div class="col-md-6">
                        <div id="scanner-container">
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
            const scannerContainer = document.getElementById('scanner-container');
            const scanningText = scannerContainer.querySelector('p.text-center');

            Quagga.init({
                inputStream: {
                    name: "Live",
                    type: "LiveStream",
                    target: scannerContainer,
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

                // Hide the "Camera feed will appear here..." text
                if (scanningText) {
                    scanningText.style.display = 'none';
                }

                document.getElementById('start-scanner').disabled = true;
                document.getElementById('stop-scanner').disabled = false;
            });

            Quagga.onDetected(function(result) {
                const code = result.codeResult.code;
                document.getElementById('barcode').value = code;
                Quagga.stop();
                document.getElementById('start-scanner').disabled = false;
                document.getElementById('stop-scanner').disabled = true;
                // Optionally, you could make the text reappear here if the scanner stops without a successful scan
            });
        });

        document.getElementById('stop-scanner').addEventListener('click', function() {
            const scannerContainer = document.getElementById('scanner-container');
            const scanningText = scannerContainer.querySelector('p.text-center');

            Quagga.stop();
            document.getElementById('start-scanner').disabled = false;
            document.getElementById('stop-scanner').disabled = true;
            // Optionally, you could make the text reappear here if the scanner is stopped manually
            if (scanningText) {
                scanningText.style.display = 'block';
            }
        });
    </script>
</body>
</html>
