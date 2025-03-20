<?php
$page = new \Site\Page();
$page->requireAuth();
$can_proceed = true;

// Initialize validation objects
$siteMessage = new \Site\SiteMessage();

// Initialize Parameter Array for organizations
$find_parameters = array();
$find_parameters['status'] = array('NEW', 'ACTIVE');

// Get organizations
$organizationlist = new \Register\OrganizationList();
$organizationlist->search($find_parameters, ['count' => true]);
if ($organizationlist->error()) {
    $page->addError($organizationlist->error());
    $can_proceed = false;
}

// Get Records
$organizations = $organizationlist->search($find_parameters);
if ($organizationlist->error()) {
    $page->addError("Error finding organizations: " . $organizationlist->error());
    $can_proceed = false;
}

// Get default customer list
$customerList = new \Register\CustomerList();
$customers = $customerList->find(array('automation' => 0));
$customersSendTo = $customers;

// Validate organization_id if provided
$organization_id = $_REQUEST['organization'] ?? null;
if (!empty($organization_id) && $organization_id !== 'All') {
    if (!$siteMessage->validInteger($organization_id)) {
        $page->addError("Invalid organization ID format");
        $can_proceed = false;
    } else {
        $customersSendTo = $customers = $customerList->find(array('organization_id' => $organization_id, 'automation' => 0));
    }
}

// Get all roles
$registerRolesList = new \Register\RoleList();
$registerRole = new \Register\Role();
$userRoles = $registerRolesList->find();

// Process message submission
$method = $_REQUEST['method'] ?? '';
if ($can_proceed && $method == 'submit') {
    // Validate CSRF Token
    $csrfToken = $_POST['csrfToken'] ?? '';
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($csrfToken)) {
        $page->addError("Invalid request token");
        $can_proceed = false;
    }
    
    // Validate required fields
    $subject = $_REQUEST['subject'] ?? '';
    $content = $_REQUEST['content'] ?? '';
    $selectSendTo = $_REQUEST['selectSendTo'] ?? [];
    
    if (empty($subject)) {
        $page->addError("Subject is required");
        $can_proceed = false;
    } elseif (!$siteMessage->validText($subject)) {
        $page->addError("Invalid subject format");
        $can_proceed = false;
    }
    
    if (empty($content)) {
        $page->addError("Message content is required");
        $can_proceed = false;
    } elseif (!$siteMessage->validText($content)) {
        $page->addError("Invalid message content format");
        $can_proceed = false;
    }
    
    if (empty($selectSendTo)) {
        $page->addError("Please select at least one recipient type (role or customer)");
        $can_proceed = false;
    }
    
    // Validate customer_id if provided
    $customer_id = $_REQUEST['customer'] ?? 'All';
    if ($customer_id !== 'All' && !$siteMessage->validInteger($customer_id)) {
        $page->addError("Invalid customer ID format");
        $can_proceed = false;
    }
    
    // Validate role_id if provided
    $role_id = $_REQUEST['role'] ?? 'All';
    if ($role_id !== 'All' && !$siteMessage->validInteger($role_id)) {
        $page->addError("Invalid role ID format");
        $can_proceed = false;
    }
    
    if ($can_proceed) {
        // Create message details
        $siteMessageDetails = array(
            'user_created' => $GLOBALS['_SESSION_']->customer->id,
            'subject' => $subject,
            'content' => $content,
            'important' => !empty($_REQUEST['important']) ? 1 : 0
        );
        
        $siteMessageDelivery = new \Site\SiteMessageDelivery();
        
        // Filter recipients based on selection
        if (count($selectSendTo) > 1) {
            // Both role and customer selected
            if ($customer_id != "All") {
                $customer = new \Register\Customer($customer_id);
                if (!$customer->id) {
                    $page->addError("Selected customer not found");
                    $can_proceed = false;
                } else {
                    $customersSendTo = array($customer);
                }
            }

            if ($can_proceed && $role_id != "All") {
                $customersInRole = array();
                foreach ($customersSendTo as $customer) {
                    $inRole = $registerRole->checkIfUserInRole($customer->id, $role_id);
                    if ($inRole) $customersInRole[] = $customer;
                }
                $customersSendTo = $customersInRole;
            }
        } else {
            // Either role or customer selected
            if (in_array('role', $selectSendTo)) {
                if ($role_id != "All") {
                    $customersInRole = array();
                    foreach ($customersSendTo as $customer) {
                        $inRole = $registerRole->checkIfUserInRole($customer->id, $role_id);
                        if ($inRole) $customersInRole[] = $customer;
                    }
                    $customersSendTo = $customersInRole;
                }
            } else if (in_array('customer', $selectSendTo)) {
                if ($customer_id != "All") {
                    $customer = new \Register\Customer();
                    $customer->get($customer_id);
                    if (!$customer->id) {
                        $page->addError("Selected customer not found");
                        $can_proceed = false;
                    } else {
                        $customersSendTo = array($customer);
                    }
                }
            }
        }
        
        if ($can_proceed) {
            if (empty($customersSendTo)) {
                $page->addError("No customers found to send message to.");
                $can_proceed = false;
            } else {
                // Create new message for each customer
                $success_count = 0;
                foreach ($customersSendTo as $customer) {
                    $siteMessageDetails['recipient_id'] = $customer->id;
                    $result = $siteMessage->add($siteMessageDetails);
                    if ($result) {
                        $success_count++;
                    } else if ($siteMessage->error()) {
                        $page->addError("Error sending to " . $customer->full_name() . ": " . $siteMessage->error());
                    }
                }
                
                if ($success_count > 0) {
                    $page->appendSuccess("Message sent to " . $success_count . " customers");
                }
            }
        }
    }
}
