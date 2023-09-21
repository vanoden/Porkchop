<?php
	$page = new \Site\Page();
	$page->requireAuth();
	
	// customer list in organization
	$customerList = new \Register\CustomerList();
	$customersInOrg = $customerList->find(array('organization_id' => $GLOBALS['_SESSION_']->customer->organization()->id, 'automation' => 0));
	
    // roles list in organization
	$customersInRoles = array();
	foreach ($customersInOrg as $customer) $customersInRoles[] = $customer->id;

	// get all the roles that belong to this organization
	$registerRole = new \Register\Role();
    $rolesUsersIn = $registerRole->getRolesforUsers($customersInRoles);
    
    // process sending user messages
    if (isset($_REQUEST['method']) && $_REQUEST['method'] == 'submit') {
    
        // Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	        $page->addError("Invalid request");
        } else {

            // create new message
            $siteMessage = new \Site\SiteMessage();
            $siteMessageDetails = array('user_created' => $GLOBALS['_SESSION_']->customer->id, 'subject' => $_REQUEST['subject'], 'content' => $_REQUEST['content']);
            $siteMessageDelivery = new \Site\SiteMessageDelivery();

            // apply important flag or not
            $siteMessageDetails['important'] = 0;
            if (!empty($_REQUEST['important'])) $siteMessageDetails['important'] = 1;
            
            // send messages to all in role
            if (isset($_REQUEST['selectSendTo']) && $_REQUEST['selectSendTo'] == 'role') {
                $customersInRole = array();
                foreach ($customersInOrg as $customer) {
                    $inRole = $registerRole->checkIfUserInRole($customer->id, $_REQUEST['role']);
                    if ($inRole) {
                        $siteMessageDetails['recipient_id'] = $customer->id;
                        $siteMessageDetails = $siteMessage->add($siteMessageDetails);
                        $siteMessageDelivery->add(array('message_id' => $siteMessageDetails->id,'user_id' => $siteMessageDetails['recipient_id']));
                    }
                }
            }
            
            // send messages to customer in organization
            if (isset($_REQUEST['selectSendTo']) && $_REQUEST['selectSendTo'] == 'customer') {
                $siteMessageDetails['recipient_id'] = $_REQUEST['customer'];
                $siteMessage->add($siteMessageDetails);
                $siteMessageDelivery->add(array('message_id' => $siteMessage->id,'user_id' => $_REQUEST['customer']));
            }
            
            $page->success = 'Message sent to specfied users';
        }
    }
    
