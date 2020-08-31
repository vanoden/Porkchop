<?php
	$page = new \Site\Page('spectros','admin_credits');
	$page->requireRole('monitor admin');

	if (! isset($GLOBALS['_config']->spectros->calibration_product)) $page->addError("Calibration Product not configured");
	else {
		$cal_product = new \Product\Item();
		$cal_product->get($GLOBALS['_config']->spectros->calibration_product);
		if (! $cal_product->id) $page->addError("Calibration Product ".$GLOBALS['_config']->spectros->calibration_product." not found");
		else {
			if ($_REQUEST['organization_id']) {
				$organization = new \Spectros\Organization($_REQUEST['organization_id']);
				$product = $organization->product($cal_product->id);
				if ($product->error) $page->addError("Error finding calibration verification credits: ".$product->error());
				else {
					if (isset($_REQUEST['btn_submit'])) {
						if ((preg_match('/^\d+$/',$_REQUEST['add_credits'])) and ($_REQUEST['add_credits'] > 0)) {
							$product->add($_REQUEST['add_credits'],array('note' => $_REQUEST['note']));
							if ($product->error()) {
								$page->addError("Error adding credits: ".$product->error());
							}
							else {
								$page->success = $_REQUEST['add_credits']." added successfully";
							}
						}
					}
					$credits = $product->count();
				}

				$audit_log = new \Spectros\CalibrationVerification\AuditLog();
				$audit_records = $audit_log->findRecords(array('organization_id' => $organization->id,'_limit' => 10));
				if ($audit_log->error()) {
					$page->addError($audit_log->error());
				}
			}
			else $credits = 0;
		}
	}

	# Get Organizations
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();
