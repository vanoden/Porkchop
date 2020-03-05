<?php
	$page = new \Site\Page();
	$page->requireRole('engineering user');

	$product = new \Engineering\Product();
	
	if ($_REQUEST['product_id']) {
		$product = new \Engineering\Product($_REQUEST['product_id']);
	}
	elseif (isset($_REQUEST['code'])) {
		$product->get($_REQUEST['code']);
		if ($product->error) $page->error = $product->error;
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$product->get($code);
	}
	
	if ($_REQUEST['btn_submit'] == 'Submit') {
	
		$parameters = array();
		if (isset($_REQUEST['title'])) $parameters['title'] = $_REQUEST['title'];
		else {
			$page->error = "Title required";
		}
		if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
		if (isset($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];

		app_log("Submitted product form",'debug',__FILE__,__LINE__);
		if ($product->id) {
			if ($product->update($parameters)) {
				$page->success = "Updates applied";
				app_log("Product updated",'debug',__FILE__,__LINE__);
			}
			else {
				$page->error = "Error saving updates: ".$product->error();
			}
		}
		else {
			if ($product->add($parameters)) {
				$page->success = "Product Created";
				app_log("Product created",'debug',__FILE__,__LINE__);
			}
			else {
				$page->error = "Error creating product: ".$product->error();
			}
		}
	}

	if ($product->id) {
		$product->details();
		$form['code'] = $product->code;
		$form['title'] = $product->title;
		$form['description'] = $product->description;
	} elseif ($page->error) {
		$form['code'] = $_REQUEST['code'];
		$form['title'] = $_REQUEST['title'];
		$form['description'] = $_REQUEST['description'];
	}
	
    // upload files if upload button is pressed
    if ($_REQUEST['btn_submit'] == 'Upload') {

	    $file = new \Storage\File();
	    $parameters = array();
        $parameters['repository_name'] = $_REQUEST['repository_name'];
        $parameters['type'] = $_REQUEST['type'];
        $parameters['ref_id'] = $product->id;
	    $uploadResponse = $file->upload($parameters);
	    
	    if (!empty($file->error)) $page->addError($file->error);
	    if (!empty($file->success)) $page->success = $file->success;
	}
	
	$filesList = new \Storage\FileList();
	$filesUploaded = $filesList->find(array('type' => 'engineering product', 'ref_id' => $product->id));

