<?php
$width = 1000;
$height = 500;
$image = imagecreatetruecolor($width, $height);
$white = imagecolorallocate($image, 255, 255, 255);
$gray = imagecolorallocate($image, 200, 200, 200);
imagefill($image, 0, 0, $white);
imagesetthickness($image, 5);
$text = 'COPY VIEW';
$font = 5;
$fontWidth = imagefontwidth($font);
$fontHeight = imagefontheight($font);
$textWidth = $fontWidth * strlen($text);
$textHeight = $fontHeight;
$x = ($width - $textWidth) / 2;
$y = ($height - $textHeight) / 2;
imagestring($image, $font, $x, $y, $text, $gray);
imagepng($image, 'images/copyView.png');
imagedestroy($image);
echo 'Image created successfully.';
?>
