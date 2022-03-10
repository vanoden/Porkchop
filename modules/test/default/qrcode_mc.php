<?php
	$qrcode = new \GoogleAPI\QRCode();
	$qrcode->create(array('content' => 'https://www.spectrosinstruments.com/_register/admin_account'));
#	print($qrcode->url());
	if (!$qrcode->download()) {
		print "Error downloading chart: ".$qrcode->error();
		print "<br>\n".$qrcode->url()."\n";
	}
	else {
		header("Content-type: image/png");
		readfile($qrcode->filePath());
	}
	exit;
