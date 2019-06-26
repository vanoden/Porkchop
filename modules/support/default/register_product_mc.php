<?php
    $page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');
	
    $productList = new \Product\ItemList();
	$productsAvailable = $productList->find(array('type' => 'unique','status' => 'active'));

    // form values
    $productId = 0;
    $selectedProduct = 0;
    $purchased = '';
    $distributor = '';
    $serialNumber = '';
    
    // if form submit
	if ($_REQUEST['btnSubmit']) {
        if ($GLOBALS['_SESSION_']->customer->id) {

            $insertParams = array();
            $insertParams['product_id']         = $productId    = $_REQUEST['productId'];
            $insertParams['date_purchased']     = $purchased    = $_REQUEST['purchased'];
            $insertParams['serial_number']      = $serialNumber = $_REQUEST['serialNumber'];
            $insertParams['distributor_name']   = $distributor  = $_REQUEST['distributor'];
            $insertParams['customer_id']    = $GLOBALS['_SESSION_']->customer->id;
            $selectedProduct                = $_REQUEST['productId'];

            // insert the potential registration record
            $registrationQueue = new \Support\RegistrationQueue();
            $registrationQueue->add($insertParams);
        } else {
            $page->error = "User login required for saving warrenty information";
            exit();
        }
        $page->success = "Thank you! Your warranty information has been submitted. Our support staff will finalize our records shortly.";
	}
