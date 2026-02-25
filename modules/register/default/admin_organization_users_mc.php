<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage customers');
	
	if (!empty($_REQUEST['id']) && empty($_REQUEST['organization_id'])) $_REQUEST['organization_id'] = $_REQUEST['id'];

	# Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
		if (isset($_REQUEST['organization_id']) && preg_match('/^\d+$/',$_REQUEST['organization_id'])) {
			$organization = new \Register\Organization($_REQUEST['organization_id']);
			if ($organization->error()) $page->addError("Unable to load organization: ".$organization->error());
		}
		elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$organization = new \Register\Organization();
			if ($organization->validCode($code)) {
				$organization->get($code);
				if (! $organization->id) $page->addError("Organization not found");
			}
			else {
				$page->addError("Invalid organization code");
			}
		}
		else $organization = new \Register\Organization();
	}
	else $organization = $GLOBALS['_SESSION_']->customer->organization();

    // handle form submit
	if (!empty($_REQUEST['method'])) {
	    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	        $page->addError("Invalid Request");
	    }
		else {
            if ($_REQUEST['method'] == 'Add User') {
                $present_customer = new \Register\Customer();

                # Make Sure Login is unique
                if ($present_customer->get($_REQUEST['new_login'])) {
                    $page->addError("Login already exists");
                }
                elseif(!$present_customer->validLogin($_REQUEST['new_login'])) {
                    $page->addError("Invalid login");
                }
                else {
                    $customer = new \Register\Customer();
                    $customer->add(
                        array(
                            "code"			=> $_REQUEST['new_login'],
                            "first_name"	=> noXSS(trim($_REQUEST['new_first_name'])),
                            "last_name"		=> noXSS(trim($_REQUEST['new_last_name'])),
                            "organization_id"	=> $organization->id,
                            "password"			=> uniqid()
                        )
                    );
                    if ($customer->error()) {
                        $page->addError("Error adding customer to organization: ".$customer->error());
                    }
                    else {
                        $page->appendSuccess("Customer added to organization");
                    }
                }
            }
	    }		
	}

	if ($organization->id) {
		$user = new \Register\Person();
		$status = array();
		if (isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) $status = $user->statii();
		
		$members = $organization->members('human', $status);
		if ($organization->error()) {
			$page->addError("Error finding human members: ".$organization->error());
			app_log("Error finding members: ".$organization->error(),'error',__FILE__,__LINE__);
		}

		$automationMembers = $organization->members('automation', $status);
		if ($organization->error()) {
			$page->addError("Error finding automation members: ".$organization->error());
			app_log("Error finding members: ".$organization->error(),'error',__FILE__,__LINE__);
		}
	}

	// Set page title and admin menu section
	$page->title = "Organization Users";
	$page->setAdminMenuSection("Customer");  // Keep Customer section open
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Organizations", "/_register/admin_organizations");
	if (isset($organization->id)) {
		$page->addBreadcrumb($organization->name, "/_register/admin_organization?organization_id=".$organization->id);
	}
	$page->addBreadcrumb("Users");
