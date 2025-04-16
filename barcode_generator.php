<?php
function generateBarcodeImage($barcode, $filename) {
    // Create directory if it doesn't exist
    if (!file_exists(dirname($filename))) {
        mkdir(dirname($filename), 0777, true);
    }
    
    // Create a blank image
    $width = 300;
    $height = 100;
    $image = imagecreate($width, $height);
    
    // Set colors
    $white = imagecolorallocate($image, 255, 255, 255);
    $black = imagecolorallocate($image, 0, 0, 0);
    
    // Fill background
    imagefilledrectangle($image, 0, 0, $width, $height, $white);
    
    // Add barcode text
    $font = 5; // Built-in GD font
    $textWidth = imagefontwidth($font) * strlen($barcode);
    $x = ($width - $textWidth) / 2;
    $y = ($height - imagefontheight($font)) / 2;
    imagestring($image, $font, $x, $y, $barcode, $black);
    
    // Save image
    imagepng($image, $filename);
    imagedestroy($image);
}
?>