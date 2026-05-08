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

// Show success message if email was just sent (after redirect)
if (isset($_GET['emailSent']) && $_GET['emailSent'] == '1') {
    $page->success = "User was issued another verification email.";
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
        // Ensure customer details are loaded
        if (!$customer->id || !$customer->details()) {
            $page->addError("Customer not found");
            app_log("Customer not found for ID: " . $verifyAgain, 'error');
            $can_proceed = false;
        } elseif (!$customer->code) {
            $page->addError("Customer login not found");
            app_log("Customer login not found for ID: " . $verifyAgain, 'error');
            $can_proceed = false;
        } else {
            $validation_key = md5(microtime());
            app_log("Generating new validation key for customer " . $verifyAgain . " (login: " . $customer->code . "): " . $validation_key, 'debug');
            if (!$customer->update(array('validation_key' => $validation_key))) {
                $page->addError("Error updating validation key: " . $customer->error());
                app_log("Error updating validation key for customer " . $verifyAgain . ": " . $customer->error(), 'error');
                $can_proceed = false;
            } else {
                app_log("Successfully updated validation key for customer " . $verifyAgain, 'debug');
                
                // create the verify account email
                $hostname = $_config->site->hostname ?? $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
                $verify_url = $hostname . '/_register/new_customer?method=verify&access=' . $validation_key . '&login=' . $customer->code;
                $verify_url = ($_config->site->https ?? false ? "https://" : "http://") . $verify_url;
                
                // Get template path with fallback
                $templatePath = $_config->register->verify_email->template ?? (defined('TEMPLATES') ? TEMPLATES : BASE.'/templates') . '/registration/verify_email.html';
                
                $template = new \Content\Template\Shell(
                    array(
                        'path'    => $templatePath,
                        'parameters'    => array(
                            'VERIFYING.URL' => $verify_url,
                            'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? ''
                        )
                    )
                );
                
                $message = new \Email\Message($_config->register->verify_email);
                $message->html(true);
                $message->body($template->output());
                $message->from($_config->register->verify_email->from ?? 'no-reply@spectrosinstruments.com');
                $message->subject($_config->register->verify_email->subject ?? 'Verify your Email');
                
                if (! $customer->notify($message)) {
                    $page->addError("Error: Confirmation email could not be sent: " . $customer->error());
                } else {
                    // Redirect to remove verifyAgain parameter and preserve search parameters
                    $redirectParams = array();
                    if (!empty($searchTerm)) $redirectParams['search'] = $searchTerm;
                    if (!empty($dateStart)) $redirectParams['dateStart'] = $dateStart;
                    if (!empty($dateEnd)) $redirectParams['dateEnd'] = $dateEnd;
                    if (in_array('VERIFYING', $statusFiltered)) $redirectParams['VERIFYING'] = 'VERIFYING';
                    if (in_array('PENDING', $statusFiltered)) $redirectParams['PENDING'] = 'PENDING';
                    if (in_array('APPROVED', $statusFiltered)) $redirectParams['APPROVED'] = 'APPROVED';
                    if (in_array('DENIED', $statusFiltered)) $redirectParams['DENIED'] = 'DENIED';
                    $redirectParams['emailSent'] = '1';
                    $redirectUrl = '/_register/pending_customers?' . http_build_query($redirectParams);
                    header("Location: " . $redirectUrl);
                    exit;
                }
            }
        }
    }
}

$possibleStatii = $queueObj->statii();

$page->title = "Pending Customers";
$page->setAdminMenuSection("Customer");  // Keep Customer section open
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Pending Customers","/_register/pending_customers");

$_REQUEST['search'] = $search ?? '';
