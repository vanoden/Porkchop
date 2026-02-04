<?php

	$site = new \Site();
	$page = $site->page();

	if (!empty($_REQUEST['year']) && !empty($_REQUEST['month'])) {
		$yearInt = intval($_REQUEST['year']);
		$year = new \Calendar\Year($yearInt);
		$monthInt = intval($_REQUEST['month']);
		$month = $year->month($monthInt);
	}
	else {
		$yearInt = date('Y');
		$year = new \Calendar\Year($yearInt);
		$monthInt = date('n');
		$month = $year->month($monthInt);
	}

	$weeks = $month->weeks();
?>