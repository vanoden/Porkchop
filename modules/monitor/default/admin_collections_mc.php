<?
	# Customers to display at a time
	if (preg_match('/^\d+$/',$_REQUEST['page_size']))
		$collections_per_page = $_REQUEST['page_size'];
	else
		$collections_per_page = 25;

	if (! $GLOBALS['_SESSION_']->authenticated()){
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}

	# Handle Deletes
	if ($_REQUEST['delete_collection']) {
		$GLOBALS['_page']->success = "Deleted Collection ".$_REQUEST['delete_collection'];
		$collection = new \Monitor\Collection($_REQUEST['delete_collection']);
		$collection->update(array("status" => "DELETED"));
		if ($collection->error) $GLOBALS['_page']->error = "Error updating collection, admins contacted";
	}

	$find_parameters = array();
	if ($_REQUEST['organization_id'])
		$find_parameters['organization_id'] = $_REQUEST['organization_id'];
	if (get_mysql_date($_REQUEST['date_start']))
		$find_parameters['date_start'] = get_mysql_date($_REQUEST['date_start']);
	if (get_mysql_date($_REQUEST['date_end']))
		$find_parameters['date_end'] = get_mysql_date($_REQUEST['date_end']);
		
	# Get Jobs
	$collectionlist = new \Monitor\CollectionList();
	$collections = $collectionlist->find($find_parameters);
	$total_collections = $collectionlist->count;
	if (! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;

	# Add Pagination to Query
	$find_parameters["_limit"] = $collections_per_page;
	$find_parameters["_offset"] = $_REQUEST['start'];

	$collections = $collectionlist->find($find_parameters);
	$total_collections = $collectionlist->count;

	if ($_REQUEST['start'] < $collections_per_page)
		$prev_offset = 0;
	else
		$prev_offset = $_REQUEST['start'] - $collections_per_page;
	$next_offset = $_REQUEST['start'] + $collections_per_page;
	$last_offset = $total_collections - $collections_per_page;

	if ($next_offset > count($collections)) $next_offset = $_REQUEST['start'] + count($collections);

	# Get Organizations
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();
?>
