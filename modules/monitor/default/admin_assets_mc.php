<?
	$page = new \Site\Page();
	if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) return;

	# Collections to display at a time
	if (preg_match('/^\d+$/',$_REQUEST['page_size']))
		$assets_per_page = $_REQUEST['page_size'];
	else
		$assets_per_page = 18;
	if (! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;

	$parameters = array();
	if (isset($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
	if (isset($_REQUEST['product_id'])) $parameters['product_id'] = $_REQUEST['product_id'];
	if (isset($_REQUEST['organization_id'])) $parameters['organization_id'] = $_REQUEST['organization_id'];

	# Get Asset Count Before Pagination
	$assetlist = new \Monitor\AssetList();
	$assetlist->find($parameters,false);
	if ($assetlist->error) {
		$page->error = "Error finding assets: ".$assetlist->error;
	}
	$total_assets = $assetlist->count;

	# Pagination
	$parameters["_limit"] = $assets_per_page;
	$parameters["_offset"] = $_REQUEST['start'];

	# Sort
	if ($_REQUEST['sort_order'] != 'DESC') $_REQUEST['sort_order'] = 'ASC';
	if (in_array($_REQUEST['sort'],array('serial','product','organization'))) {
		$parameters['_sort'] = $_REQUEST['sort'];
		$parameters['_sort_order'] = $_REQUEST['sort_order'];
	}

	# Get Assets
	$assets = $assetlist->find($parameters);

	if ($_REQUEST['start'] < $assets_per_page)
		$prev_offset = 0;
	else
		$prev_offset = $_REQUEST['start'] - $assets_per_page;
	$next_offset = $_REQUEST['start'] + $assets_per_page;
	$last_offset = $total_assets - $assets_per_page;

	if ($next_offset > count($assets)) $next_offset = $_REQUEST['start'] + count($assets);

	# Reference Information
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->findArray();

	$productlist = new \Product\ItemList();
	$products = $productlist->find(
		array(
			"type"	=> "unique"
		)
	);
	if ($productlist->error) {
		$GLOBALS['_page']->error = "Error getting products for selection: ".$productlist->error;
	}
?>
