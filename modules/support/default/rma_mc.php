<?
	$page = new \Site\Page();
	$page->requireRole("support user");

	if (isset($_REQUEST['id'])) {
		$rma = new \Support\Request\Item\RMA($_REQUEST['id']);
	}
	elseif (isset($_REQUEST['code'])) {
		$rma = new \Support\Request\Item\RMA();
		$rma->get($_REQUEST['code']);
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$rma = new \Support\Request\Item\RMA();
		$rma->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

	if ($rma->exists()) {
		$events = $rma->events();
	}
	else {
		$events = array();
	}
?>