<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage shipments');

	$recordsPerPage = 20;

	$shipmentList = new \Shipping\ShipmentList();
	$parameters = [];
	$controls = array(
		'limit' => $recordsPerPage,
		'offset' => $_REQUEST['pagination_start_id']
	);
	if (!empty($_REQUEST['sort_field'])) {
		$controls['sort'] = $_REQUEST['sort_field'];
		$controls['direction'] = $_REQUEST['sort_direction'];
	}
	$totalRecords = $shipmentList->count($parameters);
	$shipments = $shipmentList->find($parameters,$controls);
	if ($shipmentList->error()) {
		$page->addError($shipmentList->error());
	}

	$page->title("Shipments");
	$page->addBreadcrumb('Shipping');
	$page->addBreadcrumb('Shipments');

	// paginate results
    $pagination = new \Site\Page\Pagination();
    $pagination->forwardParameters(array('sort_field','sort_direction','filtered'));
    $pagination->size($recordsPerPage);
    $pagination->count($totalRecords);