<?php
	###############################################
	### /_barcode/read							###
	### Take an uploaded image and read barcode	###
	### A. Caravello 5/12/2019					###
	###############################################
	$page = new \Site\Page();
	$can_proceed = true;

	$tmp_file = "/tmp/image_" . getmypid() . ".png";

	if (isset($_REQUEST['method'])) {
		// Validate file upload
		if (!isset($_FILES['barcode']) || !is_array($_FILES['barcode'])) {
			$page->addError("No file uploaded");
			$can_proceed = false;
		} elseif ($_FILES['barcode']['error'] > 0) {
			$page->addError("File upload error: " . $_FILES['barcode']['error']);
			app_log("Error: " . $_FILES['barcode']['error']);
			$can_proceed = false;
		} else {
			app_log("Barcode file received: " . $_FILES['barcode']['size'] . " bytes");
			
			if ($can_proceed) {
				// Validate file size (e.g., max 5MB)
				if ($_FILES['barcode']['size'] > 5 * 1024 * 1024) {
					$page->addError("File too large. Maximum size is 5MB");
					$can_proceed = false;
				}
				
				// Validate file type
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime_type = finfo_file($finfo, $_FILES['barcode']['tmp_name']);
				finfo_close($finfo);
				
				if (!in_array($mime_type, ['image/png', 'image/jpeg', 'image/gif'])) {
					$page->addError("Invalid file type. Only PNG, JPEG, and GIF files are allowed");
					$can_proceed = false;
				}
				
				if ($can_proceed) {
					// Convert back to binary file
					$content = file_get_contents($_FILES["barcode"]["tmp_name"]);
					if (preg_match('/base64/', $content)) {
						$content = str_replace("data:image/png;base64,", "", $content);
						file_put_contents($tmp_file, base64_decode($content));
					} else {
						file_put_contents($tmp_file, $content);
					}
					
					// Process Image
					$zbarcode = new \Service\ZBarCode();
					if ($zbarcode->readBarCode($tmp_file)) {
						// Done with Uploaded File
						unlink($tmp_file);
						app_log("Success: Type: " . $zbarcode->type() . " Code: " . $zbarcode->code());
						$barcode = (object) array(
							'type' => $zbarcode->type(),
							'code' => $zbarcode->code()
						);
						
						// Build Response
						$response = new \HTTP\Response();
						$response->success = 1;
						$response->barcode = $barcode;
					} else {
						$response = new \HTTP\Response();
						$response->success = 0;
						$response->error = $zbarcode->error();
						error_log($zbarcode->error());
					}
					
					// Send Response
					print formatOutput($response);
					exit;
				}
			}
		}
	}

	function formatOutput($object) {
		// Validate format parameter
		$format = isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json' ? 'json' : 'xml';
		
		// Set appropriate content type
		header('Content-Type: ' . ($format == 'json' ? 'application/json' : 'application/xml'));
		
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
