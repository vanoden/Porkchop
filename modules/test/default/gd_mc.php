<?php
	header("Content-type: image/png");
	$string = "Howdy";
	$image  = imagecreatefrompng(BASE."/assets/report/magnify.png");
	$orange = imagecolorallocate($image,220,210,60);
	$px = (imagesx($image) - 7.5 * strlen($string)) / 2;
	imagestring($image, 3, $px, 9, $string, $orange);
	imagepng($image);
	imagedestroy($image);
	exit;
?>