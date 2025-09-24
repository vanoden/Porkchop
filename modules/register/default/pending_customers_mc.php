<?php
$page = new \Site\Page();
$page->requirePrivilege('manage customers');
$can_proceed = true;

// Create objects for validation
$queueObj = new \Register\Queue();

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
$action = $_REQUEST['action'] ?? null;
if (isset($action) && $action == 'updateNotes') {
    $csrfToken = $_REQUEST['csrfToken'] ?? null;
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
        $page->addError("Invalid Request");
        $can_proceed = false;
    } else {
        $id = $_REQUEST['id'] ?? null;
        if (empty($id) || !$queueObj->validInteger($id)) {
            $page->addError("Invalid customer ID");
            $can_proceed = false;
        } else {
            $notes = $_REQUEST['notes'] ?? '';
            $queuedCustomer = new Register\Queue($id);
            $queuedCustomer->update(array('notes' => noXSS(trim($notes))));
            $page->success = 'Notes updated successfully.';
        }
    }
}

// update customer status from UI request
app_log("updateStatus");
if (isset($action) && $action == 'updateStatus') {
    $csrfToken = $_REQUEST['csrfToken'] ?? null;
    $id = $_REQUEST['id'] ?? null;
    $status = $_REQUEST['status'] ?? null;

    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
        $page->addError("Invalid Request");
        $can_proceed = false;
    } elseif (empty($id) || !$queueObj->validInteger($id)) {
        $page->addError("Invalid customer ID");
        $can_proceed = false;
    } elseif (empty($status) || !$queueObj->validStatus($status)) {
        $page->addError("Invalid Status");
        $can_proceed = false;
    } else {
        $queuedCustomer = new Register\Queue($id);
        $queuedCustomer->update(array('status' => $status));
        if ($status == 'APPROVED') $queuedCustomer->syncLiveAccount();
        $page->success = 'Status updated successfully.';
    }
}

// deny customer
app_log("denyCustomer");
if (isset($action) && $action == 'denyCustomer') {
    $csrfToken = $_REQUEST['csrfToken'] ?? null;
    $id = $_REQUEST['id'] ?? null;

    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
        $page->addError("Invalid Request");
        $can_proceed = false;
    } elseif (empty($id) || !$queueObj->validInteger($id)) {
        $page->addError("Invalid customer ID");
        $can_proceed = false;
    } else {
        $queuedCustomer = new Register\Queue($id);
        $queuedCustomer->update(array('status' => 'DENIED'));
        $page->success = 'Customer denied.';
    }
}

// assign customer and/or generate new organization if needed
app_log("assignCustomer");
if (isset($action) && $action == 'assignCustomer') {
    $csrfToken = $_REQUEST['csrfToken'] ?? null;
    $id = $_REQUEST['id'] ?? null;

    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
        $page->addError("Invalid Request");
        $can_proceed = false;
    } elseif (empty($id) || !$queueObj->validInteger($id)) {
        $page->addError("Invalid customer ID");
        $can_proceed = false;
    } else {
        $queuedCustomer = new Register\Queue($id);
        $customer = new \Register\Customer($queuedCustomer->customer()->id);
        if (!$customer->exists()) {
            $page->addError("Customer not found");
            $can_proceed = false;
        } else {
            $queuedCustomer->update(array('status' => 'APPROVED'));
            $queuedCustomer->syncLiveAccount();
            $page->success = "Registration complete for " . $customer->code;
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
$verifying = $_REQUEST['VERIFYING'] ?? null;
if (!empty($verifying)) {
    if (!$queueObj->validStatus($verifying)) {
        $page->addError("Invalid VERIFYING status parameter");
        $can_proceed = false;
    } else {
        $statusFiltered[] = $verifying;
    }
}

$pending = $_REQUEST['PENDING'] ?? null;
if (!empty($pending)) {
    if (!$queueObj->validStatus($pending)) {
        $page->addError("Invalid PENDING status parameter");
        $can_proceed = false;
    } else {
        $statusFiltered[] = $pending;
    }
}

$approved = $_REQUEST['APPROVED'] ?? null;
if (!empty($approved)) {
    if (!$queueObj->validStatus($approved)) {
        $page->addError("Invalid APPROVED status parameter");
        $can_proceed = false;
    } else {
        $statusFiltered[] = $approved;
    }
}

$denied = $_REQUEST['DENIED'] ?? null;
if (!empty($denied)) {
    if (!$queueObj->validStatus($denied)) {
        $page->addError("Invalid DENIED status parameter");
        $can_proceed = false;
    } else {
        $statusFiltered[] = $denied;
    }
}

$search = $_REQUEST['search'] ?? '';
if (!empty($search) && !$queueObj->validSearch($search)) {
    $page->addError("Invalid search parameter");
    $can_proceed = false;
} else {
    $searchTerm = $search;
}

$dateStart = $_REQUEST['dateStart'] ?? '';
if (!empty($dateStart) && !$queueObj->validDate($dateStart)) {
    $page->addError("Invalid start date format");
    $can_proceed = false;
}

$dateEnd = $_REQUEST['dateEnd'] ?? '';
if (!empty($dateEnd) && !$queueObj->validDate($dateEnd)) {
    $page->addError("Invalid end date format");
    $can_proceed = false;
}

// set to default of no options selected - show only PENDING by default
if (empty($statusFiltered)) {
	$_REQUEST['PENDING'] = $statusFiltered[] = 'PENDING';
}

// get results
app_log("Find");
if ($can_proceed) {
    $queuedCustomersList = $queuedCustomers->find(
        array(
            'searchAll' => $searchTerm,
            'status' => $statusFiltered,
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd
        )
    );
}

// handle send another verification email
app_log("Verify Again");
$verifyAgain = $_GET['verifyAgain'] ?? null;
if (!empty($verifyAgain)) {
    $csrfToken = $_REQUEST['csrfToken'] ?? null;
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
        $page->addError("Invalid Request");
        $can_proceed = false;
    } elseif (!$queueObj->validInteger($verifyAgain)) {
        $page->addError("Invalid customer ID for verification");
        $can_proceed = false;
    } else {
        $customer = new \Register\Customer($verifyAgain);
        $validation_key = md5(microtime());
        $customer->update(array('validation_key' => $validation_key));

        // create the verify account email
        $verify_url = $_config->site->hostname . '/_register/new_customer?method=verify&access=' . $validation_key . '&login=' . $customer->code;
        if ($_config->site->https) $verify_url = "https://$verify_url";
        else $verify_url = "http://$verify_url";
        $template = new \Content\Template\Shell(
            array(
                'path'    => $_config->register->verify_email->template,
                'parameters'    => array(
                    'VERIFYING.URL' => $verify_url,
                    'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? ''
                )
            )
        );
        if ($template->error()) {
            app_log($template->error(), 'error');
            $page->addError("Error: generating verification email");
            $can_proceed = false;
        } else {
            $message = new \Email\Message($_config->register->verify_email);
            $message->html(true);
            $message->body($template->output());
            if (! $customer->notify($message)) {
                $page->addError("Error: Confirmation email could not be sent");
                app_log("Error: sending confirmation email: " . $customer->error(), 'error');
                $can_proceed = false;
            } else {
                $page->success = "User was issued another verification email.";
            }
        }
    }
}

$possibleStatii = $queueObj->statii();

$_REQUEST['search'] = $search ?? '';
