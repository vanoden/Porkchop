<?php
	###############################################
	### /_barcode/read							###
	### Take an uploaded image and read barcode	###
	### A. Caravello 5/12/2019					###
	###############################################
	$page = new \Site\Page();

	$tmp_file = "/tmp/image_".getmypid().".png";

	if (isset($_REQUEST['method'])) {
		if (isset($_FILES['barcode']['error']) && $_FILES['barcode']['error'] > 0) {
			app_log("Error: ".$_FILES['barcode']['error']);
			$page->addError($_FILES['barcode']['error']);
		}
		else {
			app_log("Barcode file received: ".$_FILES['barcode']['size']." bytes");
	
			# Convert back to binary file
			$content = file_get_contents($_FILES["barcode"]["tmp_name"]);
			if (preg_match('/base64/',$content)) {
				$content = str_replace("data:image/png;base64,", "", $content);
				file_put_contents($tmp_file,base64_decode($content));
			}
			else {
				file_put_contents($tmp_file,$content);
			}
	
			# Gonna Send a response
			$response = new \HTTP\Response();
	
			# Process Image
			$zbarcode = new \Service\ZBarCode();
			if ($zbarcode->readBarCode($tmp_file)) {
				# Done with Uploaded File
				unlink($tmp_file);
				app_log("Success: Type: ".$zbarcode->type()." Code: ".$zbarcode->code());
				$barcode = (object) array(
					'type'  => $zbarcode->type(),
					'code'  => $zbarcode->code()
				);
	
				# Build Response
				$response->success = 1;
				$response->barcode = $barcode;
		
			}
			else {
				$response->success = 0;
				$response->error = $zbarcode->error();
				error_log($zbarcode->error());
			}
			# Send Response
			print formatOutput($response);
			exit;
		}
	}

	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		}
		else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
