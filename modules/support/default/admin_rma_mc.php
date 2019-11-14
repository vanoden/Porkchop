<?
	$page = new \Site\Page();
	$page->requireRole('support user');

	if ($_REQUEST['rma_code']) {
		$rma = new \Support\Request\Item\RMA();
		$rma->get($_REQUEST['rma_code']);
	}
	elseif ($_REQUEST['id']) {
		$rma = new \Support\Request\Item\RMA($_REQUEST['id']);
	}
	else {
		$rma = new \Support\Request\Item\RMA();
		$rma->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

	if (! $rma->id) {
		$page->addError("RMA Not Found");
	}
	else {
        $item = $rma->item();
		$tech = $rma->approvedBy();
        $customer = $item->request()->customer;
		$shipments = $rma->shipments();
	}
