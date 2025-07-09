<?php
	###################################################
	### organization_mc.php							###
	### This program collects organization info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################
	$page = new \Site\Page();
	$page->requireOrganization();

	$organization = $GLOBALS['_SESSION_']->customer()->organization();

    // handle form submit
	if (!empty($_REQUEST['method'])) {
	    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	        $page->addError("Invalid Request");
	    }
		else {
            $page->appendSuccess($_REQUEST['method']);
		    if (! is_numeric($_REQUEST['password_expiration_days'])) $_REQUEST['password_expiration_days'] = 0;
		    $parameters = array(
			    "password_expiration_days"	=> $_REQUEST['password_expiration_days'],
				"website_url"				=> $_REQUEST['website_url'],
				"time_based_password"		=> $_REQUEST['time_based_password'],
		    );
		    if (! $_REQUEST['time_based_password']) $parameters['time_based_password'] = 0;
		    app_log("Updating '".$organization->name."'",'debug',__FILE__,__LINE__);
				    
		    // Update Existing Organization
		    $organization->update($parameters);

		    if ($organization->error()) {
			    $page->addError("Error updating organization");
		    }
			else {
			    $page->appendSuccess("Organization Updated Successfully");
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
