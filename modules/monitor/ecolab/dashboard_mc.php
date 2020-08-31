<?php	
	$page = new \Site\Page();
	$page->requireAuth();

	$collection = new \Monitor\Collection();
	if (isset($_REQUEST['collection'])) {
		if ($collection->get($_REQUEST['collection'])) {
			$dashboard = $collection->dashboard();
		}
		else {
			$page->addError($collection->error());
			return;
		}
	}
	elseif(preg_match('/^[a-f\d]{13}$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
		if ($collection->get($GLOBALS['_REQUEST_']->query_vars_array[0])) {
			app_log("Got collection ".$GLOBALS['_REQUEST_']->query_vars_array[0]);
			$dashboard = $collection->dashboard();
		}
	}
	if (empty($dashboard) || ! $dashboard->exists()) {
		$dashboard = new \Monitor\Dashboard();
		$dashboard->get($GLOBALS['_config']->monitor->default_dashboard);
	}

	if ($dashboard->exists()) {
		if (isset($dashboard->template)) {
			#app_log("Loading dashboard '".$GLOBALS['_config']->monitor->default_dashboard);
			if (file_exists(HTML.$dashboard->template)) {
				print file_get_contents(HTML.$dashboard->template);
				exit;
			}
			else {
				$page->addError("Dashboard Template '".HTML.$dashboard->template."' Not Available");
			}
		}
		else {
			$page->addError("Template not defined for dashboard '".$dashboard->name."'");
		}
	}
	elseif ($dashboard->name) {
		$page->addError("Dashboard '".$dashboard->name."' Not Found");
	}
	else {
		$page->addError("Dashboard not identified and not default configured");
	}
