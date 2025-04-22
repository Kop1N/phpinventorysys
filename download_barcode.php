<?php
if (!isset($_GET['barcode'])) {
    http_response_code(400);
    echo "Barcode not specified.";
    exit;
}

$barcode = basename($_GET['barcode']); // Sanitize filename
$file = "barcodes/" . $barcode . ".png";

if (!file_exists($file)) {
    http_response_code(404);
    echo "Barcode image not found.";
    exit;
}

header('Content-Description: File Transfer');
header('Content-Type: image/png');
header('Content-Disposition: attachment; filename="' . $barcode . '.png"');
header('Content-Length: ' . filesize($file));
readfile($file);
exit;
