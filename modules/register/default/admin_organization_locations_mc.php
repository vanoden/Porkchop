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

	$locations = array();
	if ($organization->id) {
		// get locations for organization
		$locations = $organization->locations();
		if ($organization->error()) {
			$page->addError("Error finding organization locations: ".$organization->error());
			app_log("Error finding organization locations: ".$organization->error(),'error',__FILE__,__LINE__);
		}
		if (!is_array($locations)) {
			$locations = array();
		}

		// Update Existing Organization default billing
		if (!empty($_REQUEST['setDefaultBilling']) && is_numeric($_REQUEST['setDefaultBilling'])) {
		    $old_billing_location_id = $organization->default_billing_location_id;
		    $new_billing_location_id = $_REQUEST['setDefaultBilling'];
		    
		    $updateParameters = array();
		    $updateParameters['default_billing_location_id'] = $new_billing_location_id;
		    $organization->update($updateParameters);
		    if ($organization->error()) {
			    $page->addError("Error updating organization");
		    } else {
			    // Log the change to organization audit log
			    $old_location_name = "None";
			    if ($old_billing_location_id) {
			        $old_location = new \Register\Location($old_billing_location_id);
			        if ($old_location->id) {
			            $old_location_name = $old_location->name;
			        }
			    }
			    $new_location = new \Register\Location($new_billing_location_id);
			    $new_location_name = $new_location->id ? $new_location->name : "Unknown";
			    
			    $audit_notes = "Default billing location changed from '{$old_location_name}' to '{$new_location_name}'";
			    $organization->auditRecord('ORGANIZATION_UPDATED', $audit_notes);
			    
			    $page->appendSuccess("Organization Updated Successfully");
		    }		
		}
		
		// Update Existing Organization default shipping
        if (!empty($_REQUEST['setDefaultShipping']) && is_numeric($_REQUEST['setDefaultShipping'])) {
		    $old_shipping_location_id = $organization->default_shipping_location_id;
		    $new_shipping_location_id = $_REQUEST['setDefaultShipping'];
		    
		    $updateParameters = array();
		    $updateParameters['default_shipping_location_id'] = $new_shipping_location_id;
		    $organization->update($updateParameters);
		    if ($organization->error()) {
			    $page->addError("Error updating organization");
		    } else {
			    // Log the change to organization audit log
			    $old_location_name = "None";
			    if ($old_shipping_location_id) {
			        $old_location = new \Register\Location($old_shipping_location_id);
			        if ($old_location->id) {
			            $old_location_name = $old_location->name;
			        }
			    }
			    $new_location = new \Register\Location($new_shipping_location_id);
			    $new_location_name = $new_location->id ? $new_location->name : "Unknown";
			    
			    $audit_notes = "Default shipping location changed from '{$old_location_name}' to '{$new_location_name}'";
			    $organization->auditRecord('ORGANIZATION_UPDATED', $audit_notes);
			    
			    $page->appendSuccess("Organization Updated Successfully");
		    }
		}
	}

	// Set page title and admin menu section
	$page->title = "Organization Locations";
	$page->setAdminMenuSection("Customer");  // Keep Customer section open
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Organizations", "/_register/organizations");
	if (isset($organization->id)) {
		$page->addBreadcrumb($organization->name, "/_register/admin_organization?organization_id=".$organization->id);
	}
	$page->addBreadcrumb("Locations");
