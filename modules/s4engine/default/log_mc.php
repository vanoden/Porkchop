<?php
	$site = new \Site();
	$page = $site->page();
	$page->requireAuth();

	$log = new \S4Engine\Log();

	$logRecords = $log->find(array(),array(),array('sort'=>'time_created DESC','limit'=>20));
	if ($log->error()) {
		$page->addError("Loading log records: ".$log->error());
	}

	$page->title("S4Engine Message Log");
?>