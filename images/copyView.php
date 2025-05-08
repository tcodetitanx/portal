<?php
// Create a simple watermark image
$width = 1000;
$height = 500;
$image = imagecreatetruecolor($width, $height);
$transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
imagefill($image, 0, 0, $transparent);
$gray = imagecolorallocatealpha($image, 150, 150, 150, 80);
imagesetthickness($image, 5);

// Set the text
$text = 'COPY VIEW';
$font_size = 80;
$angle = 45;

// Calculate text position for diagonal placement
$bbox = imagettfbbox($font_size, $angle, __DIR__ . '/arial.ttf', $text);
$x = ($width - ($bbox[2] - $bbox[0])) / 2;
$y = ($height - ($bbox[1] - $bbox[7])) / 2;

// Add the text
imagettftext($image, $font_size, $angle, $x, $y, $gray, __DIR__ . '/arial.ttf', $text);

// Save the image
imagepng($image, __DIR__ . '/copyView.png');
imagedestroy($image);
echo 'Image created successfully.';
?>
