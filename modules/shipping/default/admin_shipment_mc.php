<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage shipments');
	$shipment = new \Shipping\Shipment($_REQUEST['id']);
	
    if (!empty($_REQUEST['action_type'])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
		}
		else {
            if (empty($shipment->vendor_id) && !empty($_REQUEST['vendor_id'])) {
                $vendor = new \Shipping\Vendor($_REQUEST['vendor_id']);
                if ($vendor->exists()) {
                    $shipment->ship(array('vendor_id' => $_REQUEST['vendor_id']));
                    $page->success = 'Shipment Shipped';
                }
                else {
                    $page->addError("Shipping vendor not found");
                }
            }
            if ($_REQUEST['action_type'] == "receive") {
                $package = new \Shipping\Package($_REQUEST['package_id']);
                if ($package->exists()) {
                    foreach ($package->items() as $item) {
                        if (! $item->update(array('condition' => $_REQUEST['item_condition'][$item->id]))) {
                            $page->addError("Error updating item ".$item->id.": ".$item->error());
                        }
                        if ($_REQUEST['item_condition'][$item->id] == 'MISSING' && $_REQUEST['package_condition'][$package->id] != 'DAMAGED') {
                            $_REQUEST['package_condition'][$package->id] = 'INCOMPLETE';
                        }
                    }
                    if (! $page->errorCount()) {
                        if (! $package->update(array('status' => 'RECEIVED', 'condition' => $_REQUEST['package_condition'][$package->id]))) {
                            $page->addError("Error updating package ".$package->id.": ".$package->error());
                        }
                        else $page->success = 'Package Received';
                    }
                }
                else {
                    $page->addError("Package not found");
                }
            }
            elseif ($_REQUEST['action_type'] == "lost") {
                $package = new \Shipping\Package($_REQUEST['package_id']);
                if ($package->exists()) {
                    foreach ($package->items() as $item) {
                        $item->update(array('condition' => 'MISSING'));
                    }
                    $package->update(array('status' => 'LOST'));
                    $page->success = 'Package Lost';
                }
                else {
                    $page->addError("Package not found");
                }
            }
            elseif ($_REQUEST['action_type'] == "ship") {
                $package = new \Shipping\Package($_REQUEST['package_id']);
                if ($package->exists()) {
                    $package->update(array('status' => 'SHIPPED'));
                    $page->success = 'Package Shipped';
                }
                else {
                    $page->addError("Package not found");
                }
            }
            elseif ($_REQUEST['action_type'] == "close") {
                if ($shipment->ok_to_close()) {
                    if ($shipment->close()) $page->success = 'Shipment Closed';
                    else $page->addError("Error closing shipment: ".$shipment->error());
                }
                else {
                    $page->addError($shipment->error());
                }
            }
	    }
	}

	$vendorList = new \Shipping\VendorList();
	$vendors = $vendorList->find();
	$packages = $shipment->packages();

    if (preg_match('/^RMA(\d+)$/',$shipment->document_number,$matches)) {
        $object_id = $matches[1] * 1;
        $object_link = "/_support/admin_rma?id=$object_id";
    }
    elseif (preg_match('/^TCKT(\d+)$/',$shipment->document_number,$matches)) {
        $object_id = $matches[1] * 1;
        $object_link = "/_support/request_item?id=$object_id";
    }
    elseif (preg_match('/^PO(\d+)$/',$shipment->document_number,$matches)) {
        $object_id = $matches[1] * 1;
        $object_link = "/_sales/purchase_order?id=$object_id";
    }

	if (empty($shipment->vendor_id)) $shippingVendor = 'Not provided';
	else $shippingVendor = $shipment->vendor();

    $from_location = $shipment->send_location();
    $to_location = $shipment->rec_location();

    $page->title = "Shipment Detail";
	$page->addBreadcrumb("Shipments", "/_shipping/admin_shipments");
	if (isset($shipment->id)) {
		$page->addBreadcrumb($shipment->document_number);
	}
