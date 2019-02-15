<?
	$page = new \Site\Page('spectros','admin_collections');

	# Collections to display at a time
	if (preg_match('/^\d+$/',$_REQUEST['page_size']))
		$collections_per_page = $_REQUEST['page_size'];
	else
		$collections_per_page = 18;
	if (! preg_match('/^\d+$/',$_REQUEST['start'])) $_REQUEST['start'] = 0;

	# Load Modules
	if (! $GLOBALS['_SESSION_']->customer->id) {
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}

	# Handle Deletes
	if ($_REQUEST['delete_collection']) {
		$page->success = "Deleted Collection ".$_REQUEST['delete_collection'];
		$collection = new \Monitor\Collection($_REQUEST['delete_collection']);
		$collection->update(array("status" => "DELETED"));
		if ($collection->error) $page->error = "Error updating collection, admins contacted";
	}

	# Filters
	$parameters = array();
	if ($_REQUEST['organization_id'])
		$parameters['organization_id'] = $_REQUEST['organization_id'];
	if (get_mysql_date($_REQUEST['date_start']))
		$parameters['date_start'] = get_mysql_date($_REQUEST['date_start']);
	if (get_mysql_date($_REQUEST['date_end']))
		$parameters['date_end'] = get_mysql_date($_REQUEST['date_end']);
	if (preg_match('/^[A-Z]+$/',$_REQUEST['status']))
		$parameters['status'] = $_REQUEST['status'];

	# Get Job Count Before Pagination
	$collectionlist = new \Monitor\CollectionList();
	$collections = $collectionlist->find($parameters,false);
	$total_collections = $collectionlist->count;

	# Pagination
	$parameters["_limit"] = $collections_per_page;
	$parameters["_offset"] = $_REQUEST['start'];

	# Sort
	if ($_REQUEST['sort_order'] != 'DESC') $_REQUEST['sort_order'] = 'ASC';
	if (in_array($_REQUEST['sort'],array('organization','date_start','date_end','name','status'))) {
		$parameters['_sort'] = $_REQUEST['sort'];
		$parameters['_sort_order'] = $_REQUEST['sort_order'];
	}

	# Get Jobs
	$collections = $collectionlist->find($parameters);

	if ($_REQUEST['start'] < $organizations_per_page)
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
