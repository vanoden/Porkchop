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

	if ($organization->id) {
		// get locations for organization
		$locationList = new \Register\LocationList();
		$locations = $locationList->find(array("organization_id" => $organization->id));
		if ($locationList->error()) {
			$page->addError("Error finding organization locations: ".$locationList->error());
			app_log("Error finding organization locations: ".$locationList->error(),'error',__FILE__,__LINE__);
		}

		// Update Existing Organization default billing
		if (!empty($_REQUEST['setDefaultBilling']) && is_numeric($_REQUEST['setDefaultBilling'])) {
		    $updateParameters = array();
		    $updateParameters['default_billing_location_id'] = $_REQUEST['setDefaultBilling'];
		    $organization->update($updateParameters);
		    if ($organization->error()) {
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
		    if ($organization->error()) {
			    $page->addError("Error updating organization");
		    } else {
			    $page->appendSuccess("Organization Updated Successfully");
		    }
		}
	}
