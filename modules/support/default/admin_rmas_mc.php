<?
	$page = new \Site\Page();
	$page->requireRole("support user");

	$parameters = array();
	if (isset($_REQUEST['status']) && !empty($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
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

	$rmaList = new \Support\Request\Item\RMAList();
	$rmas = $rmaList->find($parameters);
	if ($rmaList->error()) {
		$page->addError($rmaList->error());
	}

	$organizationList = new \Register\OrganizationList();
	$organizations = $organizationList->find();

	$productList = new \Product\ItemList();
	$products = $productList->find();
?>
