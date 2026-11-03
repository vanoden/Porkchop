<?php
	$unsubscribe_key = '';
	if (isset($_REQUEST['unsubscribe_key']) && is_string($_REQUEST['unsubscribe_key'])) {
		$unsubscribe_key = $_REQUEST['unsubscribe_key'];
	}

	if (!isset($GLOBALS['_page']) || !is_object($GLOBALS['_page'])) {
		$GLOBALS['_page'] = new \stdClass();
	}
	if (!isset($GLOBALS['_page']->error)) {
		$GLOBALS['_page']->error = '';
	}
