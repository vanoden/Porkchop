<?php
	$page = new \Site\Page('spectros','admin_credits');
	$page->requireRole('administrator');

	if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) {
		$GLOBALS['_page']->error = "Must have role 'monitor admin' to access this page";
		return;
	}

	if (! isset($GLOBALS['_config']->spectros->calibration_product)) error("Calibration Product not configured");
	$cal_product = new \Product\Item();
	$cal_product->get($GLOBALS['_config']->spectros->calibration_product);
	if (! $cal_product->id) error("Calibration Product ".$GLOBALS['_config']->spectros->calibration_product." not found");

	if ($_REQUEST['organization_id']) {
		$organization = new \Register\Organization($_REQUEST['organization_id']);
		$product = $organization->product($cal_product->id);
		if ($product->error) app_error("Error finding calibration verification credits: ".$product->error,__FILE__,__LINE__);

		if ($_REQUEST['btn_submit']) {
			if ((preg_match('/^\d+$/',$_REQUEST['add_credits'])) and ($_REQUEST['add_credits'] > 0)) {
				$product->add($_REQUEST['add_credits']);
				if ($product->error) {
					$page->error = "Error adding credits: ".$product->error;
				}
				else {
					$page->success = $_REQUEST['add_credits']." added successfully";
				}
			}
		}
		$credits = $product->count();
	}
	else $credits = 0;

	# Get Organizations
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();
?>
