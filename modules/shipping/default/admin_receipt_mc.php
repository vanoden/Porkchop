<?php
$site = new \Site();
$page = $site->page();
$can_proceed = true;

// Create RMA object for validation
$rma = new \Support\Request\Item\RMA();

if (!empty($_REQUEST['rma_number'])) {
	// Validate CSRF token
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
		$page->addError("Invalid Request");
		$can_proceed = false;
	} else {
		// Validate RMA number format
		$rma_number = $_REQUEST['rma_number'];
		$rma_id = $rma->extractRmaId($rma_number);

		if ($rma_id === null) {
			$page->addError("Invalid RMA number format");
			$can_proceed = false;
		} else {
			$rma = new \Support\Request\Item\RMA($rma_id);
			if (!$rma->exists()) {
				$page->addError('RMA not found');
				$can_proceed = false;
			} else {
				$shipment = $rma->shipment();
				if (!$shipment->exists()) {
					$page->addError('There is no shipment for this RMA.<br>The customer must be contacted for shipping information.');
					$can_proceed = false;
				} else {
					$page->success = "Found RMA " . $rma->number();
					header('Location: /_shipping/admin_shipment?id=' . $shipment->id);
					exit;
				}
			}
		}
	}
} else {
	$page->instructions = "Enter an RMA number to find the shipment";
}

// Always return false if there was a validation error
if (!$can_proceed) {
	return false;
}
