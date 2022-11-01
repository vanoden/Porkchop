<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requirePrivilege('browse support tickets');

	if ($_REQUEST['filtered']) {
		$parameters = array('status' => array());
		if ($_REQUEST['status_new']) array_push($parameters['status'],'NEW');
		if ($_REQUEST['status_active']) array_push($parameters['status'],'ACTIVE');
		if ($_REQUEST['status_pending_customer']) array_push($parameters['status'],'PENDING_CUSTOMER');
		if ($_REQUEST['status_pending_vendor']) array_push($parameters['status'],'PENDING_VENDOR');
		if ($_REQUEST['status_complete']) array_push($parameters['status'],'COMPLETE');
		if ($_REQUEST['status_closed']) array_push($parameters['status'],'CLOSED');
		if ($_REQUEST['min_date']) {
    		$parameters['min_date'] = $_REQUEST['min_date'];
    		$minDate = $_REQUEST['min_date'];
		}
	} else {
		$parameters = array(
			'status'	=> array(
				'NEW','ACTIVE','PENDING_CUSTOMER','PENDING_VENDOR'
			)
		);
		$_REQUEST['status_new'] = true;
		$_REQUEST['status_active'] = true;
		$_REQUEST['status_pending_customer'] = true;
		$_REQUEST['status_pending_vendor'] = true;
	}

	// get if the user has filtered on product or serial
	if (isset($_REQUEST['product_id']) && $_REQUEST['product_id'] !== 'ALL') $selectedProduct = $parameters['product_id'] = $_REQUEST['product_id'];
	if (isset($_REQUEST['serial_number']) && $_REQUEST['serial_number'] !== 'ALL') $selectedSerialNumber = $parameters['serial_number'] = $_REQUEST['serial_number'];
	if (isset($_REQUEST['organization_id']) && $_REQUEST['organization_id'] !== 'ALL') $selectedOrganization = $parameters['organization_id'] = $_REQUEST['organization_id'];

    // get items based on current search
    $parameters['sort_by'] = 'ticket';
    if (!empty($_REQUEST['sort_by'])) $parameters['sort_by'] = $_REQUEST['sort_by'];
    if (!empty($_REQUEST['sort_direction'])) $parameters['sort_direction'] = $_REQUEST['sort_direction'];
	$itemlist = new \Support\Request\ItemList();
	$items = $itemlist->find($parameters);
	if ($itemlist->error()) $page->addError($itemlist->error());

    // get current serial numbers and products available
	$productList = new \Product\ItemList();
	$products = $productList->find(array('type' => 'unique','status' => 'ACTIVE'));
	
	// register_organizations
	$registerOrganizations = new \Register\OrganizationList();
	$registerOrganizations = $registerOrganizations->find(array('status'=>array('ACTIVE','NEW')), 1);
