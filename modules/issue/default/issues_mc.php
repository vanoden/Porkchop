<?php
	if (! $GLOBALS['_SESSION_']->authenticated()) {
		header("location: /_register/login");
		ob_flush();
		exit;
	}
	$issuelist = new \Issue\IssueList();
	$issues = $issuelist->find(array("organization_id" => $GLOBALS['_SESSION_']->customer->organization->id));
	if ($issuelist->error()) {
		$page->error = $issuelist->error();
	}
?>