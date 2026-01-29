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

	$csrfOk = true;
	if (isset($_REQUEST['method']) && $_REQUEST['method'] === 'Apply') {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
			$page->addError("Invalid Request");
			$csrfOk = false;
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

		// Initialize Parameters for the form
		$parameters = array();

		// Update Existing Organization default billing
		if (!empty($_REQUEST['setDefaultBilling']) && is_numeric($_REQUEST['setDefaultBilling']))
		    $parameters['default_billing_location_id'] = $_REQUEST['setDefaultBilling'];
	
		// Update Existing Organization default shipping
        if (!empty($_REQUEST['setDefaultShipping']) && is_numeric($_REQUEST['setDefaultShipping'])) {
		    $parameters['default_shipping_location_id'] = $_REQUEST['setDefaultShipping'];
		}

		// Only add form fields when Apply was submitted and CSRF passed
		if ($csrfOk && isset($_REQUEST['method']) && $_REQUEST['method'] === 'Apply') {
			if (!isset($_REQUEST['password_expiration_days']) || !is_numeric($_REQUEST['password_expiration_days'])) {
				$_REQUEST['password_expiration_days'] = 0;
			}
			if (isset($_REQUEST['password_expiration_days']) && is_numeric($_REQUEST['password_expiration_days']))
				$parameters['password_expiration_days'] = $_REQUEST['password_expiration_days'];
			if (isset($_REQUEST['website_url']) && !empty($_REQUEST['website_url']))
				$parameters['website_url'] = $_REQUEST['website_url'];
			if (isset($_REQUEST['time_based_password']) && !empty($_REQUEST['time_based_password'])) {
				$parameters['time_based_password'] = $_REQUEST['time_based_password'];
				app_log("Updating '".$organization->name."'",'debug',__FILE__,__LINE__);
			}
		}

	    // Update Existing Organization (only when we have params and success/error shown only for actual update)
		if (count($parameters) > 0) {
		    $organization->update($parameters);

		    if ($organization->error()) {
			    $page->addError("Error updating organization");
		    }
			else {
			    $page->appendSuccess("Organization Updated Successfully");
		    }
		}
	}

    // get resellers
	$resellerList = new \Register\OrganizationList();
	$resellers = $resellerList->find(array("is_reseller" => true));

    // get organization locations
    $locations = array();
	if ($organization) $locations = $organization->locations();
	if ($organization && $organization->error()) $page->addError($organization->error());

	$statii = $organization->statii();

	$page->title = "Organization Details";
	$page->setAdminMenuSection("Customer");  // Keep Customer section open
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Organizations", "/_register/organizations");
	if (isset($organization->id)) {
		$page->addBreadcrumb($organization->name,"/_register/admin_organization?organization_id=".$organization->id);
	}
