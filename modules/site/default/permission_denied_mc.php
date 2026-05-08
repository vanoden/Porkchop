<?php
	/** @view /_site/permission_denied
	 * Site-wide view for permission denied message. This view is used when a user tries to access a resource they do not have permission for. It displays a simple message indicating that access is denied.
	 * This view is intentionally minimal to avoid exposing any information about the resource or permissions.
	 */
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();

	$counter = new \Site\Counter('permission_denied');
	$counter->increment();

	app_log("Permission denied: " . $_SERVER['REQUEST_URI'], 'warning');

	http_response_code(403);
	$page->addError("You do not have permission to access this resource.");
	$page->title("Permission Denied");