<?php
	$page = new \Site\Page();
	$page->requireAuth();

	$db_service = new \Database\Service();
	$db_version = $db_service->version();
	print $db_version;
