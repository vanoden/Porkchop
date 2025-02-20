<?php
$page = new \Site\Page();
$page->requirePrivilege('manage customer locations');

$input = new \Site\Input();

$location = new \Register\Location($input->request('id', 'integer'));

if ($input->request('organization_id'))
	$organization = new \Register\Organization($input->request('organization_id', 'integer'));
if ($input->request('user_id'))
	$user = new \Register\Person($input->request('user_id', 'integer'));

if ($input->request('btn_submit')) {
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($input->post('csrfToken', 'alphanumeric'))) {
		$page->addError("Invalid Request");
	} else {
		$province = new \Geography\Province($input->request('province_id', 'integer'));
		if (!$province->id) {
			$page->addError("Province '" . $input->request('province_id') . "' not found");
		} else {
			$zip_code = $input->request('zip_code', 'zip');
			$name = $input->request('name', 'name');
			$address1 = $input->request('address_1', 'address');
			$address2 = $input->request('address_2', 'address');
			$city = $input->request('city', 'city');

			if (empty($zip_code))
				$page->addError("Zip Code required");
			elseif (!preg_match('/^[\w\-\.]+$/', $zip_code))
				$page->addError("Invalid Zip Code");
			elseif (!$province->id)
				$page->addError("Province '" . $input->request('province_id') . "' not found");
			elseif (!$location->validName($name))
				$page->addError("Invalid name");
			elseif (!$location->validAddress($address1))
				$page->addError("Invalid address");
			elseif (!$location->validAddress($address2))
				$page->addError("Invalid address");
			elseif (!$location->validCity($city))
				$page->addError("Invalid city");
			else {
				$parameters = array();
				if ($location->id > 0) {
					if ($name != $location->name)
						$parameters['name'] = $name;
					if ($address1 != $location->address_1)
						$parameters['address_1'] = $address1;
					if ($address2 != $location->address_2)
						$parameters['address_2'] = $address2;
					if (isset($city) && $city != $location->city)
						$parameters['city'] = $city;
					if ($input->request('province_id', 'integer') != $location->province_id)
						$parameters['province_id'] = $input->request('province_id', 'integer');
					if ($zip_code != $location->zip_code)
						$parameters['zip_code'] = $zip_code;

					$location->update($parameters);
					if ($location->error())
						$page->addError("Error updating location " . $location->id . ": " . $location->error());
				} else {
					$parameters['name'] = $name;
					$parameters['address_1'] = $address1;
					$parameters['address_2'] = $address2;
					if (isset($city))
						$parameters['city'] = $city;
					$parameters['province_id'] = $input->request('province_id', 'integer');
					$parameters['zip_code'] = $zip_code;
					app_log("Adding location " . $parameters['name'] . " in province " . $input->request('province_id'));

					if (!$location->add($parameters)) {
						if ($location->error()) {
							$page->addError("Error adding location: " . $location->error());
						} else {
							$page->addError("Unhandled error adding location");
						}
					}
				}

				// apply any default billing or shipping set
				$default_billing = $input->request('default_billing');
				$default_shipping = $input->request('default_shipping');
				if ($default_billing || $default_shipping) {
					$location->applyDefaultBillingAndShippingAddresses(
						$input->request('organization_id', 'integer'),
						$location->id,
						$default_billing,
						$default_shipping
					);
				}

				if ($page->errorCount() < 1) {
					$org_id = $input->request('organization_id', 'integer');
					$user_id = $input->request('user_id', 'integer');

					if ($org_id && !$location->associateOrganization($org_id))
						$page->addError("Error associating organization: " . $location->error());
					if ($user_id && !$location->associateUser($user_id))
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

if ($input->request('id')) {
	$selected_province = $location->province();
	$selected_country = $selected_province->country();
	$provinces = $selected_country->provinces();
}
