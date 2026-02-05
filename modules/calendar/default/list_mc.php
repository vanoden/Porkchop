<?php
	$site = new \Site();
	$page = $site->page();

	if ($_REQUEST['calendar_id']) {
		if (!is_numeric($_REQUEST['calendar_id'])) {
			$page->invalidCode("Invalid calendar");
		}
		else {
			$calendar = new \Calendar\Calendar($_REQUEST['calendar_id']);
			if (! $calendar->exists()) {
				$page->notFound("Calendar not found");
			}
		}
	}
	else if ($GLOBALS['_request_']->query_vars_array[0]) {
		$calendar = new \Calendar\Calendar();
		if (! $calendar->validCode($GLOBALS['_request_']->query_vars_array[0])) {
			$page->invalidRequest("Invalid calendar code");
		}
		else {
			$calendar->get($GLOBALS['_request_']->query_vars_array[0]);
			if (! $calendar->exists()) {
				$page->notFound("Calendar not found");
			}
		}
	}

	if ($calendar->exists()) {
		$events = $calendar->upcomingEvents();
	}
