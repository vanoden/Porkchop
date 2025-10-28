<?php
	$site = new \Site();
	$page = $site->page();

	if (!empty ($_REQUEST['timestamp']) && get_mysql_date($_REQUEST['timestamp'])) {
		$marker = get_mysql_date($_REQUEST['timestamp']);
	}
	else {
		$marker = now();
	}

	// Get Calendar


	// Get Time Period (Day)
	$day = new \Calendar\Day($marker);
