<?php
	$page = new \Site\Page(array("module" => "spectros","view" => "calibrate"));

	if (! $_REQUEST['code']) {
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$_REQUEST['product'] = $GLOBALS['_REQUEST_']->query_vars_array[1];
	}
	if (! $_REQUEST['code']) {
		$page->error = "Asset code required";
		return;
	}

	$asset_product = new \Product\Item();
	$asset_product->get($_REQUEST['product']);
	if (! $asset_product->id) {
		$page->error = "Product not found";
		return;
	}
	$asset = new \Monitor\Asset();
	$asset->get($_REQUEST['code'],$asset_product->id);
	if (! $asset->id) {
		$page->error = "Asset '".$_REQUEST['code']."' not found";
		return;
	}

	# Show available credits
	$product = new \Spectros\CalibrationVerification\Credit($GLOBALS['_SESSION_']->customer->organization->id);
	if ($product->error) app_error("Error initializing calibration verification credits: ".$product->error,__FILE__,__LINE__);

	# See if Credits available
	$credits = $product->count();
	if ($product->error) {
		app_log("Error getting credits: ".$product->error,'error',__FILE__,__LINE__);
		$page->error = "Error getting calibration credits";
		return;
	}

	if ($credits > 0) $available_credits = $credits;
	else $available_credits = 0;

	if (isset($_REQUEST['btn_submit'])) {
		$date_calibration = get_mysql_date($_REQUEST['date_calibration']);

		# See if Credits available
		if ($credits < 1) {
			app_log("Not enough credits for organization '".$parameters['organization_id']."' [".print_r($result,true)."]",'notice',__FILE__,__LINE__);
			$page->error = "Not enough calibration credits";
			return;
		}
		# Create Verification Record
		$verification = new \Spectros\CalibrationVerification();
		if ($verification->error) {
			app_error("Error initializing calibration verification: ".$verification->error,__FILE__,__LINE__);
			$page->error = "Error recording calibration verification";
			return;
		}
		$verification->add(array("asset_id" => $asset->id,"date_request" => $date_calibration));
		if ($verification->error) {
			app_error("Error adding calibration verification: ".$verification->error,__FILE__,__LINE__);
			$page->error = "Error recording calibration verification";
			return;
		}

		# Add Metadata to Verification Record
		$verification->setMetadata("standard_manufacturer",$_REQUEST['standard_manufacturer']);
		if ($verification->error) {
			$page->error = "Error setting metadata for calibration verification: ".$verification->error;
			return;
		}
		$verification->setMetadata("standard_concentration",$_REQUEST['standard_concentration']);
		if ($verification->error) {
			$page->error = "Error setting metadata for calibration verification: ".$verification->error;
			return;
		}
		$verification->setMetadata("standard_expires",$_REQUEST['standard_expires']);
		if ($verification->error) {
			$page->error = "Error setting metadata for calibration verification: ".$verification->error;
			return;
		}
		$verification->setMetadata("monitor_reading",$_REQUEST['monitor_reading']);
		if ($verification->error) {
			$page->error = "Error setting metadata for calibration verification: ".$verification->error;
			return;
		}
		$verification->setMetadata("cylinder_number",$_REQUEST['cylinder_number']);
		if ($verification->error) {
			$page->error = "Error setting metadata for calibration verification: ".$verification->error;
			return;
		}
		$verification->setMetadata("detector_voltage",$_REQUEST['detector_voltage']);
		if ($verification->error) {
			$page->error = "Error setting metadata for calibration verification: ".$verification->error;
			return;
		}

		$verification->ready();

		# Consume 1 credit
		$product->consume(1);
		if ($product->error) app_error("Error consuming credit: ".$product->error,__FILE__,__LINE__);

		header("location: /_spectros/calibrations/".$_REQUEST['code']."/".$_REQUEST['product']);
		exit;
	}

	$date_calibration = date('m/d/Y H:i:s');
	
	function app_error($string,$file,$line) {
		$page->error = $string;
		app_log($string,'error',__FILE__,__LINE__);
		return;
	}
