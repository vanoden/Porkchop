<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage shipments');
	$can_proceed = true;

	$recordsPerPage = 20;

	$shipmentList = new \Shipping\ShipmentList();
	$parameters = [];

	// Validate pagination_start_id
	if (!empty($_REQUEST['pagination_start_id'])) {
		if (!$shipmentList->validInteger($_REQUEST['pagination_start_id'])) {
			$page->addError("Invalid pagination start ID");
			$can_proceed = false;
		}
	}

	$controls = array(
		'limit' => $recordsPerPage,
		'offset' => $_REQUEST['pagination_start_id'] ?? 0
	);

	// Validate sort parameters if provided
	if (!empty($_REQUEST['sort_field'])) {
		if (!$shipmentList->validText($_REQUEST['sort_field'])) {
			$page->addError("Invalid sort field");
			$can_proceed = false;
		} else {
			$controls['sort'] = $_REQUEST['sort_field'];
			
			// Validate sort direction
			if (!empty($_REQUEST['sort_direction'])) {
				if (!$shipmentList->validText($_REQUEST['sort_direction']) || 
					!in_array(strtoupper($_REQUEST['sort_direction']), ['ASC', 'DESC'])) {
					$page->addError("Invalid sort direction");
					$can_proceed = false;
				} else {
					$controls['order'] = strtoupper($_REQUEST['sort_direction']);
				}
			} else {
				$controls['order'] = 'ASC';
			}
		}
	}

	if ($can_proceed) {
		$totalRecords = $shipmentList->count($parameters);
		$shipments = $shipmentList->find($parameters, $controls);
		if ($shipmentList->error()) {
			$page->addError($shipmentList->error());
		}
	}

	$page->title("Shipments");
	$page->addBreadcrumb('Shipping');
	$page->addBreadcrumb('Shipments');

	// paginate results
    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('sort_field','sort_direction','filtered'));
    $pagination->size($recordsPerPage);
    $pagination->count($totalRecords ?? 0);
