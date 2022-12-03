<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage shipments');
	$shipment = new \Shipping\Shipment($_REQUEST['id']);
	
    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	    $page->addError("Invalid Request");
    }
	else {
 	    if (!empty($_REQUEST['btn_shipped'])) {
			$vendor = new \Shipping\Vendor($_REQUEST['vendor_id']);
			if ($vendor->exists()) {
			    $shipment->ship(array('vendor_id' => $_REQUEST['vendor_id']));
			    $page->success = 'Shipment Shipped';
			}
			else {
				$page->addError("Shipping vendor not found");
			}
	    }
		elseif (!empty($_REQUEST['btn_lost'])) {
		    $shipment->update(array('status' => 'LOST'));
		    $page->success = 'Shipment Status = LOST';
	    }
		elseif (!empty($_REQUEST['btn_received'])) {
		    $received = true;
		    foreach ($shipment->packages() as $package) {
			    if ($package->status != 'RECEIVED') {
				    $page->addError("Package ".$package->number." not received");
				    $received = false;
			    }
		    }
		    $shipment->update(array('status' => 'RECEIVED'));
		    $page->success = 'Shipment Status = RECEIVED';
	    }
    }

	$vendorList = new \Shipping\VendorList();
	$vendors = $vendorList->find();
	$packages = $shipment->packages();
	if (empty($shipment->vendor_id)) $shippingVendor = 'Not provided';
	else $shippingVendor = $shipment->vendor();
