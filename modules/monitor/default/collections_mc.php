<?
	if (! $GLOBALS['_SESSION_']->customer->id) {
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

	# Get Jobs
	$collectionlist = new \Monitor\CollectionList();
	$collections = $collectionlist->find();
?>
