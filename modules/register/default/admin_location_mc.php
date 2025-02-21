<?php
$page = new \Site\Page();
$page->requirePrivilege('manage customer locations');
$location = new \Register\Location($_REQUEST['id']);
if (isset($_REQUEST['organization_id']))
	$organization = new \Register\Organization($_REQUEST['organization_id']);
if (isset($_REQUEST['user_id']))
	$user = new \Register\Person($_REQUEST['user_id']);

if (isset($_REQUEST['btn_submit'])) {
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid Request");
	} else {
		// Sanitize inputs
		$request = [
			'name' => $location->sanitize($_REQUEST['name'], 'text'),
			'address_1' => $location->sanitize($_REQUEST['address_1'], 'address'),
			'address_2' => $location->sanitize($_REQUEST['address_2'], 'address'),
			'city' => $location->sanitize($_REQUEST['city'], 'text'),
			'zip_code' => $location->sanitize($_REQUEST['zip_code'], 'text'),
			'province_id' => $location->sanitize($_REQUEST['province_id'], 'integer'),
		];

		$province = new \Geography\Province($request['province_id']);
		if (!$province->id) {
			$page->addError("Province '" . $request['province_id'] . "' not found");
		} else {
			if (empty($request['zip_code']))
				$page->addError("Zip Code required");
			elseif (!preg_match('/^[\w\-\.]+$/', $request['zip_code']))
				$page->addError("Invalid Zip Code");
			elseif (!$province->id)
				$page->addError("Province '" . $request['province_id'] . "' not found");
			elseif (!$location->validName($request['name']))
				$page->addError("Invalid name");
			elseif (!$location->validAddressLine($request['address_1']))
				$page->addError("Invalid address");
			elseif (!empty($request['address_2']) && !$location->validAddressLine($request['address_2']))
				$page->addError("Invalid address");
			elseif (!$location->validCity($request['city']))
				$page->addError("Invalid city");
			else {
				$parameters = array();
				if ($location->id > 0) {
					if ($request['name'] != $location->name)
						$parameters['name'] = $request['name'];
					if ($request['address_1'] != $location->address_1)
						$parameters['address_1'] = $request['address_1'];
					if ($request['address_2'] != $location->address_2)
						$parameters['address_2'] = $request['address_2'];
					if (isset($request['city']) && $request['city'] != $location->city)
						$parameters['city'] = $request['city'];
					if ($request['province_id'] != $location->province_id)
						$parameters['province_id'] = $request['province_id'];
					if ($request['zip_code'] != $location->zip_code)
						$parameters['zip_code'] = $request['zip_code'];

					$location->update($parameters);
					if ($location->error())
						$page->addError("Error updating location " . $location->id . ": " . $location->error());
				} else {
					$parameters['name'] = $request['name'];
					$parameters['address_1'] = $request['address_1'];
					$parameters['address_2'] = $request['address_2'];
					if (isset($request['city']))
						$parameters['city'] = $request['city'];
					$parameters['province_id'] = $request['province_id'];
					$parameters['zip_code'] = $request['zip_code'];
					app_log("Adding location " . $parameters['name'] . " in province " . $request['province_id']);

					if (!$location->add($parameters)) {
						if ($location->error()) {
							$page->addError("Error adding location: " . $location->error());
						} else {
							$page->addError("Unhandled error adding location");
						}
					}
				}

				// apply any default billing or shipping set
				if (isset($_REQUEST['default_billing']) || isset($_REQUEST['default_shipping']))
					$location->applyDefaultBillingAndShippingAddresses($_REQUEST['organization_id'], $location->id, isset($_REQUEST['default_billing']), isset($_REQUEST['default_shipping']));
				if ($page->errorCount() < 1) {
					if (isset($_REQUEST['organization_id']) && !$location->associateOrganization($_REQUEST['organization_id']))
						$page->addError("Error associating organization: " . $location->error());
					if (isset($_REQUEST['user_id']) && !$location->associateUser($_REQUEST['user_id']))
						$page->addError("Error associating user: " . $location->error());
					if (!$page->errorCount())
						$page->success = "Changes saved";
				}
			}
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
