<?
	if (! $GLOBALS['_SESSION_']->authenticated()) {
		header("location: /_register/login?target=_monitor:assets");
		exit;
	}


	$dashboard = new \Monitor\Dashboard();
	$dashboard->get($GLOBALS['_config']->monitor->default_dashboard);
	if ($dashboard->exists()) {
		if (isset($dashboard->template)) {
			app_log("Loading dashboard '".$GLOBALS['_config']->monitor->default_dashboard);
			if (file_exists(HTML.$dashboard->template)) {
				print file_get_contents(HTML.$dashboard->template);
			}
			else {
				print "Dashboard Template '".HTML.$dashboard->template."' Not Available";
			}
		}
		else {
			print "Template not defined for dashboard '".$dashboard->name."'";
		}
	}
	else {
		print "Dashboard '".$dashboard->name."' Not Found";
	}
	exit;
?>
