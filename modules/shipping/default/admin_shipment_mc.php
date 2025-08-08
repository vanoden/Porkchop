<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage shipments');
	$can_proceed = true;

	// Create validation objects
	$rma = new \Support\Request\Item\RMA();
	$ticket = new \Support\Request\Item();

	// Validate shipment ID
	if (empty($_REQUEST['id'])) {
		$page->addError("Shipment ID is required");
		$can_proceed = false;
	} elseif (!$rma->validInteger($_REQUEST['id'])) {
		$page->addError("Invalid shipment ID format");
		$can_proceed = false;
	}

	if ($can_proceed) {
		$shipment = new \Shipping\Shipment($_REQUEST['id']);
		if (!$shipment->exists()) {
			$page->addError("Shipment not found");
			$can_proceed = false;
		}
	}

	// Load related objects if shipment exists
	if ($can_proceed) {
		$rma_id = $rma->extractRmaId($shipment->document_number);
		if ($rma_id !== null) {
			$rma = new \Support\Request\Item\RMA($rma_id);
			if ($rma->exists()) $ticket = $rma->item();
		} elseif (preg_match('/^TCKT(\d+)$/', $shipment->document_number, $matches)) {
			$ticket_id = $matches[1] * 1;
			$ticket = new \Support\Request\Item($ticket_id);
			if ($ticket->exists()) $rma = $ticket->rmas()[0] ?? null;
		}
	}

	// Handle form submission
	if (isset($_REQUEST['action_type'])) {
		// Validate CSRF token
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			// Handle shipping vendor assignment
			if (empty($shipment->vendor_id) && !empty($_REQUEST['vendor_id'])) {
				if (!$rma->validInteger($_REQUEST['vendor_id'])) {
					$page->addError("Invalid vendor ID format");
					$can_proceed = false;
				} else {
					$vendor = new \Shipping\Vendor($_REQUEST['vendor_id']);
					if (!$vendor->exists()) {
						$page->addError("Shipping vendor not found");
						$can_proceed = false;
					} else {
						$shipment->ship(array('vendor_id' => $_REQUEST['vendor_id']));
						if ($shipment->error()) {
							$page->addError("Error assigning vendor: " . $shipment->error());
						} else {
							$page->appendSuccess("Shipment Shipped");
						}
					}
				}
			}
			
			// Handle package actions
			if (!empty($_REQUEST['package_id'])) {
				if (!$rma->validInteger($_REQUEST['package_id'])) {
					$page->addError("Invalid package ID format");
					$can_proceed = false;
				} else {
					$package = new \Shipping\Package($_REQUEST['package_id']);
					if (!$package->exists()) {
						$page->addError("Package not found");
						$can_proceed = false;
					} else {
						switch ($_REQUEST['action_type']) {
							case 'receive':
								// Validate item conditions
								foreach ($package->items() as $item) {
									if (!empty($_REQUEST['item_condition'][$item->id])) {
										if (!$item->update(array('condition' => $_REQUEST['item_condition'][$item->id]))) {
											$page->addError("Error updating item " . $item->id . ": " . $item->error());
										}
										if ($_REQUEST['item_condition'][$item->id] == 'MISSING' && 
											($_REQUEST['package_condition'][$package->id] ?? '') != 'DAMAGED') {
											$_REQUEST['package_condition'][$package->id] = 'INCOMPLETE';
										}
									}
								}
								
								if (!$page->errorCount()) {
									if (!$package->update(array(
										'status' => 'RECEIVED',
										'condition' => $_REQUEST['package_condition'][$package->id] ?? 'GOOD'
									))) {
										$page->addError("Error updating package " . $package->id . ": " . $package->error());
									} else {
										$page->appendSuccess("Package Received");
										
										// Send notification if RMA exists
										if (isset($rma) && $rma->exists()) {
											$notification = new \Support\Notification();
											if (!$notification->send(array(
												'template' => 'ticket_update_notification',
												'subject' => "Your RMA Shipment has been received",
												'to' => 'customer',
												'ticket' => $ticket,
												'rma' => $rma,
												'description' => "We have received your shipment",
											))) {
												$page->addError("Could not notify customer: " . $notification->error());
											}
										}
									}
								}
								break;
								
							case 'lost':
								foreach ($package->items() as $item) {
									$item->update(array('condition' => 'MISSING'));
								}
								if (!$package->update(array('status' => 'LOST'))) {
									$page->addError("Error marking package as lost: " . $package->error());
								} else {
									$page->appendSuccess("Package marked as lost");
								}
								break;
								
							case 'ship':
								if (!$package->update(array('status' => 'SHIPPED'))) {
									$page->addError("Error marking package as shipped: " . $package->error());
								} else {
									$page->appendSuccess("Package marked as shipped");
								}
								break;
								
							case 'close':
								if ($shipment->ok_to_close()) {
									if (!$shipment->close()) {
										$page->addError("Error closing shipment: " . $shipment->error());
									} else {
										$page->appendSuccess("Shipment closed");
									}
								} else {
									$page->addError($shipment->error());
								}
								break;
								
							default:
								$page->addError("Invalid action type");
								break;
						}
					}
				}
			}
		}
	}

	// Load data for display
	$vendorList = new \Shipping\VendorList();
	$vendors = $vendorList->find();
	$packages = $shipment->packages();

	// Set up object links
	if (isset($shipment->document_number)) {
		$rma_id = null;
		if (isset($rma) && $rma !== null) {
			$rma_id = $rma->extractRmaId($shipment->document_number);
		}
		if ($rma_id !== null) {
			$object_id = $rma_id;
			$object_link = "/_support/admin_rma?id=$object_id";
		} elseif (preg_match('/^TCKT(\d+)$/', $shipment->document_number, $matches)) {
			$object_id = $matches[1] * 1;
			$object_link = "/_support/request_item?id=$object_id";
		} elseif (preg_match('/^PO(\d+)$/', $shipment->document_number, $matches)) {
			$object_id = $matches[1] * 1;
			$object_link = "/_sales/purchase_order?id=$object_id";
		}
	}

	// Set shipping vendor display
	$shippingVendor = empty($shipment->vendor_id) ? 'Not provided' : $shipment->vendor();

	// Get locations
	$from_location = $shipment->send_location();
	$to_location = $shipment->rec_location();

	// Set up page navigation
	$page->title("Shipment Detail");
	$page->addBreadCrumb("Warehouse");
	$page->addBreadcrumb("Shipments", "/_shipping/admin_shipments");
	if (isset($shipment->id)) {
		$page->addBreadcrumb($shipment->document_number);
	}
