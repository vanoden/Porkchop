<?php
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
	} else {
        $item = $rma->item();
		$tech = $rma->approvedBy();
        $customer = $item->request()->customer;
		$shipment = $rma->shipment();
		if ($GLOBALS['_config']->site->https) $url = "https://".$GLOBALS['_config']->site->hostname."/_support/rma_form/".$rma->code;
		else $url = "http://".$GLOBALS['_config']->site->hostname."/_support/rma_form/".$rma->code;
		$contact = $rma->billingContact();
	}
	
    // upload files if upload button is pressed
    if ($_REQUEST['btn_submit'] == 'Upload') {

	    $file = new \Storage\File();
	    $parameters = array();
        $parameters['repository_name'] = $_REQUEST['repository_name'];
        $parameters['type'] = $_REQUEST['type'];
        $parameters['ref_id'] = $rma->id;
	    $uploadResponse = $file->upload($parameters);
	    
	    if (!empty($file->error)) $page->addError($file->error);
	    if (!empty($file->success)) $page->success = $file->success;
	}
	
	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'support rma', 'ref_id' => $rma->id));
