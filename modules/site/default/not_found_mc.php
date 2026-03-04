<?php
	/** @view /_site/not_found
	 * Site-wide view for not found message. This view is used when a user tries to access a resource that does not exist. It displays a simple message indicating that the resource was not found.
	 * This view is intentionally minimal to avoid exposing any information about the resource or existence of other resources.
	 */
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();

	$counter = new \Site\Counter('not_found');
	$counter->increment();

	app_log("Resource not found: " . $_SERVER['REQUEST_URI'], 'warning');

	http_response_code(404);
	$page->addError("The resource you requested was not found.");
	$page->title("Not Found");