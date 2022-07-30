<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage customer locations');
	$location = new \Register\Location($_REQUEST['id']);
	if (isset($_REQUEST['organization_id'])) $organization = new \Register\Organization($_REQUEST['organization_id']);
	if (isset($_REQUEST['user_id'])) $user = new \Register\Person($_REQUEST['user_id']);

	if (isset($_REQUEST['btn_submit'])) {
		if (empty($_REQUEST['zip_code'])) $page->addError("Zip Code required");
		$province = new \Geography\Province($_REQUEST['province_id']);
		if (! $province->id) $page->addError("Province '".$_REQUEST['province_id']."' not found");
		else {
			$parameters = array();
			if ($location->id > 0) {
				if ($_REQUEST['name'] != $location->name) $parameters['name'] = $_REQUEST['name'];
				if ($_REQUEST['address_1'] != $location->address_1) $parameters['address_1'] = $_REQUEST['address_1'];
				if ($_REQUEST['address_2'] != $location->address_2) $parameters['address_2'] = $_REQUEST['address_2'];
				if (isset($_REQUEST['city']) && $_REQUEST['city'] != $location->city) $parameters['city'] = $_REQUEST['city'];
				if ($_REQUEST['province_id'] != $location->province_id) $parameters['province_id'] = $_REQUEST['province_id'];
				if ($_REQUEST['zip_code'] != $location->zip_code) $parameters['zip_code'] = $_REQUEST['zip_code'];
	
				$location->update($parameters);
				if ($location->error()) $page->addError("Error updating location ".$location->id.": ".$location->error());
				
				// apply any default billing or shipping set
				if ($_REQUEST['default_billing'] || $_REQUEST['default_shipping']) 
				    $location->applyDefaultBillingAndShippingAddresses($_REQUEST['organization_id'], $location->id, isset($_REQUEST['default_billing']), isset($_REQUEST['default_shipping']));
				
			} else {
				$parameters['name'] = $_REQUEST['name'];
				$parameters['address_1'] = $_REQUEST['address_1'];
				$parameters['address_2'] = $_REQUEST['address_2'];
				if (isset($_REQUEST['city'])) $parameters['city'] = $_REQUEST['city'];
				$parameters['province_id'] = $_REQUEST['province_id'];
				$parameters['zip_code'] = $_REQUEST['zip_code'];
				app_log("Adding location ".$parameters['name']." in province ".$_REQUEST['province_id']);
	
				if (! $location->add($parameters)) {
					if ($location->error()) {
						$page->addError("Error adding location: ".$location->error());
					} else {
						$page->addError("Unhandled error adding location");
					}
				}
			}
			if ($page->errorCount() < 1) {
				if (isset($_REQUEST['organization_id']) && ! $location->associateOrganization($_REQUEST['organization_id'])) $page->addError("Error associating organization: ".$location->error());
				if (isset($_REQUEST['user_id']) && !$location->associateUser($_REQUEST['user_id'])) $page->addError("Error associating user: ".$location->error());
				if (! $page->errorCount()) $page->success = "Changes saved";
			}
		}
	}

	$world = new \Geography\World();
	$countries = $world->countries();

	if ($_REQUEST['id']) {
		$selected_province = $location->province();
		$selected_country = $selected_province->country();
		$provinces = $selected_country->provinces();
	}
