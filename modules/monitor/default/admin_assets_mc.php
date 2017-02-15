<?
	if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) return;

	if ($_REQUEST['btn_submit']) {
		# Get Assets
		$assetlist = new \Monitor\AssetList();
		$assets = $assetlist->find(
			array(
				"code"				=> $_REQUEST['code'],
				"product_id"		=> $_REQUEST['product_id'],
				"organization_id"	=> $_REQUEST['organization_id'],
			)
		);
	}

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
