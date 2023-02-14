<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage terms of use');