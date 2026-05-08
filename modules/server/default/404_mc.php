<?php
	http_response_code(404);
	app_log("Can't find view ".$GLOBALS['_REQUEST_']->view." for module ".$GLOBALS['_REQUEST_']->module,'error');
	
	// Get the current page object
	$site = new \Site();
	$page = $site->page();
	
	// Set page title for 404 page
	if (empty($page->title)) {
		$page->title = "Page Not Found";
	}
	
	// Add breadcrumb
	$page->addBreadcrumb("Home", "/");
	$page->addBreadcrumb("404 - Page Not Found");