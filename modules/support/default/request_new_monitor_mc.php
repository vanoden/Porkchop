<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	$product = new \Product\Item($_REQUEST['product_id']);
	$asset = new \Monitor\Asset();
	$asset->get($_REQUEST['code'],$product->id);
	$organization = $asset->organization;
	if (! $product->id) {
		$page->addError("Product not found");
	}
	elseif (! $asset->id) {
		$page->addError("Asset not found");
	}
	elseif (! $organization->id) {
		$page->addError("Asset must be assigned to an organization");
	}
	else {
		if (isset($_REQUEST['btn_submit'])) {
			$request = new \Support\Request();
			$request->add(
				array(
					"date_request"	=> $_REQUEST['date_request'],
					"customer_id"	=> $_REQUEST['requestor_id'],
					"type"			=> 'SERVICE',
					"status"		=> $_REQUEST['status']
				)
			);
			if ($request->id) {
				$item = array(
					'line'			=> 0,
					'product_id'	=> $product->id,
					'serial_number'	=> $asset->code,
					'description'	=> $_REQUEST['line_description'],
					'quantity'		=> 1
				);
				$request->addItem($item);
				if ($request->error()) {
					$page->addError("Error adding item to request: ".$request->error());
				}
				if (! $page->errorCount()) {
					header('location: /_monitor/admin_details/'.$asset->code."/".$product->code);
					exit;
				}
			}
			elseif($request->error()) {
				$page->addError($request->error());
			}
		}
	}

	$customerlist = new \Register\CustomerList();
	$customers = $customerlist->find(array('organization_id' => $organization->id));
?>
