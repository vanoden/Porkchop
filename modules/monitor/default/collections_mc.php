<?php
	$max_records = 10;

	if (! $GLOBALS['_SESSION_']->customer->id) {
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}
	if (! $GLOBALS['_SESSION_']->customer->organization->id) {
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}

	# Handle Deletes
	if ($_REQUEST['delete_collection']) {
		$collection = new \Monitor\Collection($_REQUEST['delete_collection']);
		$collection->update(array("status" => "DELETED"));
		if ($collection->error) $GLOBALS['_page']->error = "Error updating collection, admins contacted";
		else $GLOBALS['_page']->success = "Deleted Collection ".$_REQUEST['delete_collection'];
	}

    # Filters
    $parameters = array();
	$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;

    # Get Job Count Before Pagination
    $collectionlist = new \Monitor\CollectionList();
    $collections = $collectionlist->find($parameters,false);
    $total_collections = $collectionlist->count;

    # Pagination
    $parameters["_limit"] = $max_records;
    $parameters["_offset"] = $_REQUEST['start'];

    # Sort

    # Get Jobs
    $collections = $collectionlist->find($parameters);

    if ($_REQUEST['start'] < $max_records)
        $prev_offset = 0;
    else
        $prev_offset = $_REQUEST['start'] - $max_records;
    $next_offset = $_REQUEST['start'] + $max_records;
    $last_offset = $total_collections - $max_records;

    if ($next_offset > count($collections)) $next_offset = $_REQUEST['start'] + count($collections);
