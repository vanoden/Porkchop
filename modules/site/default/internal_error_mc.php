<?php
	/** @view /_site/internal_error
	 * Site-wide view for internal server error message. This view is used when an unexpected error occurs on the server. It displays a simple message indicating that an internal error occurred.
	 * This view is intentionally minimal to avoid exposing any information about the error or server configuration.
	*/
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();

	$counter = new \Site\Counter('internal_error');
	$counter->increment();

	http_response_code(500);
	$page->addError("An internal server error occurred.");
	$page->title("Internal Server Error");