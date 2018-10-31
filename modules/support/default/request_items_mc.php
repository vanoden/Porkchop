<?
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');

	if ($_REQUEST['filtered']) {
		$parameters = array(
			'status'		=> array()
		);
		if ($_REQUEST['status_new']) array_push($parameters['status'],'NEW');
		if ($_REQUEST['status_active']) array_push($parameters['status'],'ACTIVE');
		if ($_REQUEST['status_pending_customer']) array_push($parameters['status'],'PENDING_CUSTOMER');
		if ($_REQUEST['status_pending_vendor']) array_push($parameters['status'],'PENDING_VENDOR');
		if ($_REQUEST['status_complete']) array_push($parameters['status'],'COMPLETE');
		if ($_REQUEST['status_closed']) array_push($parameters['status'],'CLOSED');
	}
	else {
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
	if ($_REQUEST['product_id']) {
		$parameters['product_id'] = $_REQUEST['product_id'];
	}
	if ($_REQUEST['serial_number']) {
		$parameters['serial_number'] = $_REQUEST['serial_number'];
	}

	$itemlist = new \Support\Request\ItemList();
	$items = $itemlist->find($parameters);
	if ($itemlist->error()) {
		$page->addError($itemlist->error());
	}
?>