<?php
session_start();

// Must have GD enabled to draw images
if (!extension_loaded('gd')) {
    header("Content-Type: text/plain");
    echo "ERROR: PHP GD extension is not enabled.\n";
    echo "To fix this, open php.ini and uncomment the line:\n";
    echo "   extension=gd\n";
    exit;
}


$width = 140;
$height = 50;

$image = imagecreatetruecolor($width, $height);

// Colors
$bg  = imagecolorallocate($image, 240, 240, 240);
$fg  = imagecolorallocate($image, 20, 20, 20);
$lineColor = imagecolorallocate($image, 180, 180, 180);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg);

// Generate random code
$captcha_code = substr(strtoupper(md5(random_bytes(8))), 0, 6);

// Store for validation
$_SESSION['captcha_code'] = $captcha_code;

// Add noise lines
for ($i = 0; $i < 5; $i++) {
    imageline(
        $image,
        rand(0, $width), rand(0, $height),
        rand(0, $width), rand(0, $height),
        $lineColor
    );
}

// Draw text
$font_size = 5; // internal GD font
imagestring($image, $font_size, 20, 15, $captcha_code, $fg);

// Output as PNG
header("Content-Type: image/png");
imagepng($image);
imagedestroy($image);
?>
