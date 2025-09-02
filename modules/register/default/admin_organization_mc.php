<?php
	/** @file admin_organization_mc.php
	 *  This program collects organization info
	 *  for the user.
	 *  A. Caravello 11/12/2002
	 */

	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage customers');
	
	if (!empty($_REQUEST['id']) && empty($_REQUEST['organization_id'])) $_REQUEST['organization_id'] = $_REQUEST['id'];

	# Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
		if (preg_match('/^\d+$/',$_REQUEST['organization_id'])) {
			$organization = new \Register\Organization($_REQUEST['organization_id']);
			if ($organization->error()) $page->addError("Unable to load organization: ".$organization->error());
		}
		elseif (preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
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
            $page->appendSuccess($_REQUEST['method']);
		    if (! $_REQUEST['name']) {
			    $page->addError("Name required");
		    }
			elseif (!$organization->validName($_REQUEST['name'])) {
				$page->addError("Invalid name");
			}
			elseif (!$organization->validStatus($_REQUEST['status'])) {
			    $page->addError("Invalid status");
		    }
			elseif (!empty($_REQUEST['code']) && !$organization->validCode($_REQUEST['code'])) {
			    $page->addError("Invalid code");
		    }
			else {
			    if (empty($_REQUEST['code'])) $_REQUEST['code'] = null;
			    if (! is_numeric($_REQUEST['password_expiration_days'])) $_REQUEST['password_expiration_days'] = 0;
			    $parameters = array(
				    "name"					    => isset($_REQUEST['name']) ? $_REQUEST['name'] : '',
				    "code"					    => isset($_REQUEST['code']) ? $_REQUEST['code'] : '',
				    "status"				    => isset($_REQUEST['status']) ? $_REQUEST['status'] : '',
				    "is_reseller"			    => isset($_REQUEST['is_reseller']) ? $_REQUEST['is_reseller'] : 0,
					"is_customer"		    	=> isset($_REQUEST['is_customer']) ? $_REQUEST['is_customer'] : 0,
					"is_vendor"			    	=> isset($_REQUEST['is_vendor']) ? $_REQUEST['is_vendor'] : 0,
				    "assigned_reseller_id"	    => isset($_REQUEST['assigned_reseller_id']) ? $_REQUEST['assigned_reseller_id'] : '',
				    "notes"					    => isset($_REQUEST['notes']) ? noXSS(trim($_REQUEST['notes'])) : '',
				    "password_expiration_days"	=> isset($_REQUEST['password_expiration_days']) ? $_REQUEST['password_expiration_days'] : 0,
					"website_url"				=> isset($_REQUEST['website_url']) ? $_REQUEST['website_url'] : '',
					"time_based_password"		=> isset($_REQUEST['time_based_password']) ? $_REQUEST['time_based_password'] : 0,
			    );
			    if (!isset($_REQUEST['is_reseller']) || ! $_REQUEST['is_reseller']) $parameters['is_reseller'] = 0;
				if (!isset($_REQUEST['is_customer']) || ! $_REQUEST['is_customer']) $parameters['is_customer'] = 0;
				if (!isset($_REQUEST['is_vendor']) || ! $_REQUEST['is_vendor']) $parameters['is_vendor'] = 0;
			    if (!isset($_REQUEST['time_based_password']) || ! $_REQUEST['time_based_password']) $parameters['time_based_password'] = 0;
			    if ($organization->id) {
				    app_log("Updating '".$organization->name."'",'debug',__FILE__,__LINE__);
				    //app_log(print_r($parameters,true),'trace',__FILE__,__LINE__);
				    
				    // Update Existing Organization
				    $organization->update($parameters);

				    if ($organization->error()) {
					    $page->addError("Error updating organization");
				    }
					else {
					    $page->appendSuccess("Organization Updated Successfully");
				    }
				    
				    if ($_REQUEST['new_login']) {
					    $present_customer = new \Register\Customer();

					    # Make Sure Login is unique
					    $present_customer->get($_REQUEST['new_login']);
					    if ($present_customer->id) {
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
				else {
				    if (empty($parameters['code'])) $parameters['code'] = uniqid();
				    app_log("Adding organization '".$parameters['name']."'");
				    # See if code used
				    $present_org = new \Register\Organization();
					if (!$present_org->validCode($parameters['code'])) {
						$page->addError("Invalid organization code");
					}
				    elseif ($present_org->get($parameters['code'])) {
						$page->addError("Organization exists with code '".$parameters['code']."'");
					}
					else {
						# Add Existing Organization
						$organization = new \Register\Organization();
						$organization->add($parameters);
						if ($organization->error()) {
							$page->addError("Error updating organization: ".$organization->error());
						}
						else {
							$page->appendSuccess("Organization ".$organization->id." Created Successfully");
						}
					}
			    }
		    }
	    }		
	}
	
	// add tag to organization
	if (!empty($_REQUEST['addTag']) && empty($_REQUEST['removeTag'])) {
	    $registerTag = new \Register\Tag();
	    if (!empty($_REQUEST['newTag']) && $registerTag->validName($_REQUEST['newTag'])) {
	        $registerTag->add(array('type'=>'ORGANIZATION','register_id'=>$_REQUEST['organization_id'],'name'=>$_REQUEST['newTag']));
			if ($registerTag->error()) {
				$page->addError("Error adding organization tag: ".$registerTag->error());
			}
			else {
				$page->appendSuccess("Organization Tag added Successfully");
			}
	    }
		else {
    	    $page->addError("Value for Organization Tag is required");
	    }
	}
	
	// remove tag from organization
	if (!empty($_REQUEST['removeTagId'])) {
        $registerTagList = new \Register\TagList();
        $organizationTags = $registerTagList->find(array("type" => "ORGANIZATION", "register_id" => $organization->id, "id"=> $_REQUEST['removeTagId']));
	    foreach ($organizationTags as $organizationTag) $organizationTag->delete();
	}

	if ($organization->id) {
		$user = new \Register\Person();
		$status = array();
		if (isset($_REQUEST['showAllUsers']) && !empty($_REQUEST['showAllUsers'])) $status = $user->statii();
		
		$members = $organization->members('human', $status);
		if ($organization->error()) {
			$page->addError("Error finding human members: ".$organization->error());
			app_log("Error finding members: ".$organization->error,'error',__FILE__,__LINE__);
		}

		$automationMembers = $organization->members('automation', $status);
		if ($organization->error()) {
			$page->addError("Error finding automation members: ".$organization->error());
			app_log("Error finding members: ".$organization->error,'error',__FILE__,__LINE__);
		}

		// Update Existing Organization default billing
		if (!empty($_REQUEST['setDefaultBilling']) && is_numeric($_REQUEST['setDefaultBilling'])) {
		    $updateParameters = array();
		    $updateParameters['default_billing_location_id'] = $_REQUEST['setDefaultBilling'];
		    $organization->update($updateParameters);
		    if ($organization->error) {
			    $page->addError("Error updating organization");
		    } else {
			    $page->appendSuccess("Organization Updated Successfully");
		    }		
		}
		
		// Update Existing Organization default shipping
        if (!empty($_REQUEST['setDefaultShipping']) && is_numeric($_REQUEST['setDefaultShipping'])) {
		    $updateParameters = array();
		    $updateParameters['default_shipping_location_id'] = $_REQUEST['setDefaultShipping'];
		    $organization->update($updateParameters);
		    if ($organization->error) {
			    $page->addError("Error updating organization");
		    } else {
			    $page->appendSuccess("Organization Updated Successfully");
		    }
		}
	}

    // get resellers
	$resellerList = new \Register\OrganizationList();
	$resellers = $resellerList->find(array("is_reseller" => true));

    // get tags for organization
    $registerTagList = new \Register\TagList();
    $organizationTags = $registerTagList->find(array("type" => "ORGANIZATION", "register_id" => $organization->id));

    // get organization locations
    $locations = array();
	if ($organization) $locations = $organization->locations();
	if ($organization && $organization->error()) $page->addError($organization->error());

	$statii = $organization->statii();

	$page->title = "Organization Details";
	$page->addBreadcrumb("Organizations", "/_register/organizations");
	if (isset($organization->id)) {
		$page->addBreadcrumb($organization->name,"/_register/admin_organization?organization_id=".$organization->id);
	}
