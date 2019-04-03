<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');
	
	$queuedCustomers = new Register\QueueList();
	$queuedCustomersList = $queuedCustomers->find(
	    array(
	        'name'=>'', 
	        'address' => '', 
	        'phone' => '',
            'code' => '',
            'status' => '',
            'date_created' => '',
            'is_reseller' => '',
            'assigned_reseller_id' => '',
            'product_id' => '',
            'serial_number' => ''
	    )
	);
	//var_dump($queuedCustomersList);
