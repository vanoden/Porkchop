<?
	if (! $GLOBALS['_SESSION_']->authenticated()) {
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}

	if (! isset($_REQUEST['code'])) {
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	}

	if (isset($_REQUEST['collection_id']) && $_REQUEST['collection_id'] > 0) {
		$collection = new \Monitor\Collection($_REQUEST['collection_id']);
	}
	elseif ($_REQUEST['code']) {
		$collection = new \Monitor\Collection();
		if (isset($_REQUEST['organization'])) {
			if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
				$organization = new \Register\Organization();
				$organization->get($_REQUEST['organization']);
			}
			else {
				error("No permissions to see other organizations data");
			}
		}
		else {
			$organization = $GLOBALS['_SESSION_']->customer->organization;
		}
		$collection->get($_REQUEST['code'],$organization->id);
		if ($collection->error) {
			app_log("Error loading collection '$collection': ".$collection->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Error loading collection";
			return null;
		}
		elseif (! $collection->code) {
			app_log("No collection found matching '".$_REQUEST['code']."'",'notice',__FILE__,__LINE__);
			$GLOBALS['_page'] = "No matching collection found";
			$collection->code = $_REQUEST['code'];
		}
	}
	else {
		$collection = new \Monitor\Collection();
	}
	# Get Sensors for Collection
	$sensors = $collection->sensors();
?>
