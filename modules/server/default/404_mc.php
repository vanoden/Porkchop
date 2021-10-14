<?php
	http_response_code(404);
	app_log("Can't find view ".$GLOBALS['_REQUEST_']->view." for module ".$GLOBALS['_REQUEST_']->module,'error');
	print "Sorry but we can't find that page.";