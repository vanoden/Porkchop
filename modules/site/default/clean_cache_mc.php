<?php
	$page = new \Site\Page();
	$page->requirePrivilege("manage cache");

	$client = $GLOBALS['_CACHE_'];

	$max_deletes = 1000;
	if (is_numeric($_REQUEST['max_deletes'])) $max_deletes = $_REQUEST['max_deletes'];

	if (is_numeric($_REQUEST['age_days'])) $_REQUEST['age_hours'] = $_REQUEST['age_days'] * 24;
	if (is_numeric($_REQUEST['age_hours'])) $hours = $_REQUEST['age_hours'];
	else $horus = 24*7;
	$age_threshold = time() - 3600 * $hours;

	$deleted = 0;

	print "Deleting up to $max_deletes records older than ".date('Y-m-d H:i:s',$age_threshold);
	$session_keys = $client->keys('session');
	foreach ($session_keys as $key) {
		//print_r($key);
		$session = $client->get($key);
		//print_r($session);

		if (preg_match('/(\d+)\-(\d+)\-(\d+)\s(\d+)\:(\d+)\:(\d+)/',$session->last_hit_date,$matches)) {
			$cmp_date = strtotime($matches[0]);
			if ($cmp_date < $age_threshold) {
				print "Deleting session: ".$session->id." from ".$session->last_hit_date."<br>\n";
				$client->delete($key);
				$max_deletes --;
				$deleted ++;
			}
		}
		if ($max_deletes < 1) break;
	}
	print "Deleted $deleted keys<br>\n";
	exit;