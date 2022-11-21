<?php
	$page = new \Site\Page();
	$page->requirePrivilege('see request RMAs');

	$parameters = array();
	$pageSize = 20;
	if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
	if ($parameters['status'] == 'ALL') unset($parameters['status']);
	
	if (isset($_REQUEST['organization_id']) && is_numeric($_REQUEST['organization_id'])) $parameters['organization_id'] = $_REQUEST['organization_id'];
	if (isset($_REQUEST['product_id']) && is_numeric($_REQUEST['product_id'])) $parameters['product_id'] = $_REQUEST['product_id'];
	if (isset($_REQUEST['date_start']) && get_mysql_date($_REQUEST['date_start'])) {
		$parameters['date_start'] = $_REQUEST['date_start'];
		$date_start = get_mysql_date($_REQUEST['date_start']);
	}
	if (isset($_REQUEST['date_end']) && get_mysql_date($_REQUEST['date_end'])) {
		$parameters['date_end'] = $_REQUEST['date_end'];
		$date_end = get_mysql_date($_REQUEST['date_end']);
	}
	
	// pagination for results
	$parameters['result_start'] = 0;
	$parameters['paginate_count'] = $pageSize;
	$currentPage = 0;
	if (isset($_REQUEST['page']) && is_numeric($_REQUEST['page']) && !isset($_REQUEST['btn_filter'])) {
    	$parameters['result_start'] = ($_REQUEST['page'] * $pageSize);
    	$currentPage = $_REQUEST['page'];
	}
	
	$rmaList = new \Support\Request\Item\RMAList();
	$rmas = $rmaList->find($parameters);
	if ($rmaList->error()) $page->addError($rmaList->error());

	$organizationList = new \Register\OrganizationList();
	$organizations = $organizationList->find();

	$productList = new \Product\ItemList();
	$products = $productList->find();
