<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage shipments');
	$input = new Input();
	$recordsPerPage = 20;

	// Initialize shipment list and use new validation method
	$shipmentList = new \Shipping\ShipmentList();
	$parameters = [];
	$controls = [];
	
	// Let the ShipmentList class handle all parameter validation
	$validation_passed = $shipmentList->validateInputParameters($input, $page, $parameters, $controls, $recordsPerPage);
	
	// Get shipments based on validated parameters
	$totalRecords = $shipmentList->count($parameters);
	$shipments = $shipmentList->find($parameters, $controls);
	if ($shipmentList->error()) $page->addError($shipmentList->error());

	$page->title("Shipments");
	$page->addBreadcrumb('Shipping');
	$page->addBreadcrumb('Shipments');

	// paginate results
    $pagination = new \Site\Page\Pagination();
    
    // Use the validated forward parameters from the ShipmentList class
    if (isset($parameters['forward_params']) && is_array($parameters['forward_params'])) {
        $pagination->forwardParameters($parameters['forward_params']);
    }
    
    $pagination->size($recordsPerPage);
    $pagination->count($totalRecords);