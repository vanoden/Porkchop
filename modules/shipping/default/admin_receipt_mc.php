<?php
	$site = new \Site();
	$page = $site->page();

	if (!empty($_REQUEST['rma_number'])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
		}
		else {
			if (preg_match('/\d+/',$_REQUEST['rma_number'])) {
				$rma_id = $_REQUEST['rma_number'];
			}
			elseif (!preg_match('/^RMA(\d+)/i',$_REQUEST['rma_number'],$matches)) {
				$rma_id = $matches[1];
			}
			
			$rma = new \Support\Request\Item\RMA($rma_id);
			if (!$rma->exists()) {
				$page->addError('RMA not found');
			}
			else {
				$shipment = $rma->shipment();
				if (!$shipment->exists()) {
					$page->addError('There is no shipment for this RMA.<br>The customer must be contacted for shipping information.');
				}
				else {
					$page->success = "Found RMA ".$rma->number();
					header('Location: /_shipping/admin_shipment?id=' . $shipment->id);
					exit;
				}
			}
		}
	}
	else {
		$page->instructions = "Enter an RMA number to find the shipment";
	}