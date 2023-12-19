<?php
$page = new \Site\Page();
$page->requireAuth();

// loop through each parameter, validate and sanitize
$request_params = ['organization', 'subject', 'content', 'customer', 'role'];
foreach($request_params as $param) {
    if(isset($_REQUEST[$param])) {
        if ($param == "subject" || $param == "content") $_REQUEST[$param] = filter_var($_REQUEST[$param], FILTER_SANITIZE_STRING);
        if ($param == "organization" || $param == "customer" || $param == "role") {
            if (!$_REQUEST[$param] == "All") $_REQUEST[$param] = filter_var($_REQUEST[$param], FILTER_SANITIZE_NUMBER_INT);
        }
    }
}

// Security - Only Register Module Operators or Managers can see other customers
$organizationlist = new \Register\OrganizationList();
$organization = new \Register\Organization();

// Initialize Parameter Array
$find_parameters = array();
$find_parameters['status'] = array('NEW', 'ACTIVE');

// Get Count before Pagination
$organizationlist->search($find_parameters, ['count' => true]);
if ($organizationlist->error()) $page->addError($organizationlist->error());

// Get Records
$organizations = $organizationlist->search($find_parameters);
if ($organizationlist->error()) $page->addError("Error finding organizations: " . $organizationlist->error());

// customer list in organization
$customerList = new \Register\CustomerList();
$customersSendTo = $customers = $customerList->find(array('automation' => 0));
if (isset($_REQUEST['organization']) && !empty($_REQUEST['organization'])) $customersSendTo = $customers = $customerList->find(array('organization_id' => $_REQUEST['organization'], 'automation' => 0));

// get all the roles that belong to this organization
$registerRolesList = new \Register\RoleList();
$registerRole = new \Register\Role();
$userRoles = $registerRolesList->find();

// process sending user messages
if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'submit') {

    // Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        $page->addError("Invalid request");
    } else {

        // create new message
        $siteMessage = new \Site\SiteMessage();
        $siteMessageDetails = array('user_created' => $GLOBALS['_SESSION_']->customer->id, 'subject' => $_REQUEST['subject'], 'content' => $_REQUEST['content']);
        $siteMessageDelivery = new \Site\SiteMessageDelivery();

        // apply important flag or not
        $siteMessageDetails['important'] = 0;
        if (!empty($_REQUEST['important'])) $siteMessageDetails['important'] = 1;

        // if selectSendTo contains 'role' , else contains 'customer' else if contains both
        if (count($_REQUEST['selectSendTo']) > 1) {

            if ($_REQUEST['customer'] != "All") {
                $customer = new \Register\Customer($_REQUEST['customer']);
                $customersSendTo = array($customer);
            }

            if ($_REQUEST['role'] != "All") {
                $customersInRole = array();
                foreach ($customersSendTo as $customer) {
                    $inRole = $registerRole->checkIfUserInRole($customer->id, $_REQUEST['role']);
                    if ($inRole) $customersInRole[] = $customer;
                }
                $customersSendTo = $customersInRole;
            }

        } else {

            if (in_array('role', $_REQUEST['selectSendTo'])) {
                if ($_REQUEST['role'] != "All") {
                    $customersInRole = array();
                    foreach ($customersSendTo as $customer) {
                        $inRole = $registerRole->checkIfUserInRole($customer->id, $_REQUEST['role']);
                        if ($inRole) $customersInRole[] = $customer;
                    }
                    $customersSendTo = $customersInRole;
                }
            } else {
                if ($_REQUEST['customer'] != "All") {
                    $customer = new \Register\Customer();
                    $customer->get($_REQUEST['customer']);
                    $customersSendTo = array($customer);
                }
            }
        }
    }
    if (empty($customersSendTo)) $page->addError("No customers found to send message to.");

    // create new message for each customer filtered
    foreach ($customersSendTo as $customer) {
        $siteMessageDetails['recipient_id'] = $customer->id;
        $siteMessage->add($siteMessageDetails);
    }

    $page->appendSuccess("Message sent to " . count($customersSendTo) . " customers");
}