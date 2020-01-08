<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	if ($_REQUEST['organization_id']) {
		$organization = new \Register\Organization($_REQUEST['organization_id']);
		if ($organization->id) {
			$customers = $organization->members('human');
			if ($organization->error) {
				$page->addError("Error finding customers: ".$organization->error);
			}
			elseif ($_REQUEST['btn_submit']) {
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
					if (isset($_REQUEST['description']) && strlen($_REQUEST['description']) > 0) {
						$parameters = array(
								'product_id'	=> 0,
								'line'			=> 0,
								'description'	=> $_REQUEST['description'],
								"quantity"		=> 0
						);
						
						$request->addItem($parameters);
						if ($request->error()) $page->addError("Error adding message: ".$request->error());						
					}
					foreach ($_REQUEST['product_id'] as $line => $pid) {
						print "<br>Line $line, Product ".$_REQUEST['product_id'][$line].", Serial ".$_REQUEST['serial_number'][$line];
						if (! $_REQUEST['product_id'][$line] && ! $_REQUEST['serial_number'][$line] && ! $_REQUEST['line_description'][$line]) continue;
						$item = array(
							'line'			=> $line,
							'product_id'	=> $_REQUEST['product_id'][$line],
							'serial_number'	=> $_REQUEST['serial_number'][$line],
							'description'	=> $_REQUEST['line_description'][$line],
							'quantity'		=> 1
						);
						$request->addItem($item);
						if ($request->error()) $page->addError("Error adding item to request: ".$request->error());
					}
					if (! $page->errorCount()) {
						header('location: /_support/request_items');
						exit;
					}
				} elseif($request->error()) {
					$page->addError($request->error());
				}
			}
		}
		else {
			$page->addError("Organization not found");
		}
	}
	
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();

	$productlist = new \Product\ItemList();
	$products = $productlist->find(array('type'=> array('inventory','unique','kit')));
