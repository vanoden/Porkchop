<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage customers');
	
	/**
	 * get color codes HEX for given queued customer status
	 * @param $status
	 */
	function colorCodeStatus($status) {
	    $color =  "#28a745";
	    switch ($status) {
	        case 'VERIFYING':
	            $color =  "#007bff";
	            break;
	        case 'PENDING':
	            $color =  "#28a745";
	            break;
	        case 'APPROVED':
	            $color =  "#333333";
	            break;
	        case 'DENIED':
	            $color =  "#dc3545";
	            break;
	        default:
	            $color =  "#28a745";
	            break;
	    }
	    return $color;
	}

    // update customer notes from UI request
    app_log("updateNotes");
	if ( isset($_REQUEST['action']) && $_REQUEST['action'] == 'updateNotes') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
        	$page->addError("Invalid Request");
        }
		else {
            $queuedCustomer = new Register\Queue($_REQUEST['id']);
            $queuedCustomer->update(array('notes' => noXSS(trim($_REQUEST['notes']))));
            $page->success = true;
        }
	}

    // update customer status from UI request
    app_log("updateStatus");
    $queuedCustomer = new Register\Queue($_REQUEST['id']);	
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'updateStatus') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
        	$page->addError("Invalid Request");
        }
		elseif (!$queuedCustomer->validStatus($_REQUEST['status'])) {
			$page->addError("Invalid Status");
		}
		else {    
            $queuedCustomer->update(array('status' => $_REQUEST['status']));
            if ($_REQUEST['status'] == 'APPROVED') $queuedCustomer->syncLiveAccount();
            $page->success = true;
        }
	}

    // assign customer and/or generate new organization if needed
    app_log("denyCustomer");
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'denyCustomer') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
        	$page->addError("Invalid Request");
        } else {
            $queuedCustomer = new Register\Queue($_REQUEST['id']);	    
            $queuedCustomer->update(array('status' => 'DENIED'));
            $page->success = true;
        }
	}

    // assign customer and/or generate new organization if needed
    app_log("assignCustomer");
	if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'assignCustomer') {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
        	$page->addError("Invalid Request");
        }
		else {
            $queuedCustomer = new Register\Queue($_REQUEST['id']);
			$customer = new \Register\Customer($queuedCustomer->customer()->id);
			if (! $customer->exists()) {
				$page->addError("Customer not found");
			}
			else {
	            $queuedCustomer->update(array('status' => 'APPROVED'));
	            $queuedCustomer->syncLiveAccount();
	            $page->success = "Registration complete for ".$customer->login;
			}
        }
	}	

    // get queued customers based on search
    app_log("QueueList");
    $queuedCustomers = new Register\QueueList();
    $searchTerm = '';
    $dateStart = '';
    $dateEnd = '';
    $statusFiltered = array();

    // process form posted filters for results
    app_log("Filters");
    if (isset($_REQUEST['VERIFYING'])) $statusFiltered[] = $_REQUEST['VERIFYING'];
    if (isset($_REQUEST['PENDING'])) $statusFiltered[] = $_REQUEST['PENDING'];
    if (isset($_REQUEST['APPROVED'])) $statusFiltered[] = $_REQUEST['APPROVED'];
    if (isset($_REQUEST['DENIED'])) $statusFiltered[] = $_REQUEST['DENIED'];
    if (isset($_REQUEST['search'])) $searchTerm = $_REQUEST['search'];
    if (isset($_REQUEST['dateStart'])) $dateStart = $_REQUEST['dateStart'];
    if (isset($_REQUEST['dateEnd'])) $dateEnd = $_REQUEST['dateEnd'];
    
    // set to default of no options selected
    if (empty($statusFiltered)) $_REQUEST['PENDING'] = $statusFiltered[] = 'PENDING';

    // get results
    app_log("Find");
    $queuedCustomersList = $queuedCustomers->find(
        array(
            'searchAll'=> $searchTerm,
            'status' => $statusFiltered, 
            'dateStart'=> $dateStart,
            'dateEnd'=> $dateEnd
        )
    );

    // handle send another verification email
    if (isset($_GET['verifyAgain']) && !empty($_GET['verifyAgain'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
        	$page->addError("Invalid Request");
        } else {
        
            $customer = new \Register\Customer($_GET['verifyAgain']);
            $validation_key = md5(microtime());
            $customer->update(array('validation_key'=>$validation_key));
        
            // create the verify account email
            $verify_url = $_config->site->hostname . '/_register/new_customer?method=verify&access=' . $validation_key . '&login=' . $customer->login;
            if ($_config->site->https) $verify_url = "https://$verify_url";
            else $verify_url = "http://$verify_url";
            $template = new \Content\Template\Shell (
                array(
                    'path'	=> $_config->register->verify_email->template,
                    'parameters'	=> array(
	                    'VERIFYING.URL' => $verify_url
                    )
                )
            );
            if ($template->error()) {
                app_log($template->error(),'error');
                $page->addError("Error: generating verification email");
            } else {
                $message = new \Email\Message($_config->register->verify_email);
                $message->html(true);
                $message->body($template->output());
                if (! $customer->notify($message)) {
                    $page->addError("Error: Confirmation email could not be sent");
                    app_log("Error: sending confirmation email: ".$customer->error(),'error');
                } else {
                    $page->success = "User was issued another verification email.";
                }
            }
        }
    }

	$possibleStatii = $queuedCustomer->statii();