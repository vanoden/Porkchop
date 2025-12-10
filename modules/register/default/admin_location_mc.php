<?php
$page = new \Site\Page();
$page->requirePrivilege('manage customer locations');
$location = new \Register\Location(isset($_REQUEST['id']) ? $_REQUEST['id'] : 0);
if (isset($_REQUEST['organization_id']))
	$organization = new \Register\Organization($_REQUEST['organization_id']);
if (isset($_REQUEST['user_id']))
	$user = new \Register\Person($_REQUEST['user_id']);

if (isset($_REQUEST['btn_submit'])) {
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid Request");
	} else {
		$province = new \Geography\Province($_REQUEST['province_id']);
		if (!$province->id) {
			$page->addError("Province '" . $_REQUEST['province_id'] . "' not found");
		} else {

			if (empty($_REQUEST['zip_code']))
				$page->addError("Zip Code required");
			elseif (!preg_match('/^[\w\-\.]+$/', $_REQUEST['zip_code']))
				$page->addError("Invalid Zip Code");
			elseif (!$province->id)
				$page->addError("Province '" . $_REQUEST['province_id'] . "' not found");
			elseif (!$location->validName($_REQUEST['name']))
				$page->addError("Invalid name");
			elseif (!$location->validAddress($_REQUEST['address_1']))
				$page->addError("Invalid address");
			elseif (!$location->validAddress($_REQUEST['address_2']))
				$page->addError("Invalid address");
			elseif (!$location->validCity($_REQUEST['city']))
				$page->addError("Invalid city");
			else {
				$parameters = array();
				$changed_fields = array();
				if ($location->id > 0) {
					if ($_REQUEST['name'] != $location->name) {
						$parameters['name'] = $_REQUEST['name'];
						$changed_fields[] = "Name: '{$location->name}' to '{$_REQUEST['name']}'";
					}
					if ($_REQUEST['address_1'] != $location->address_1) {
						$parameters['address_1'] = $_REQUEST['address_1'];
						$changed_fields[] = "Address 1: '{$location->address_1}' to '{$_REQUEST['address_1']}'";
					}
					if ($_REQUEST['address_2'] != $location->address_2) {
						$parameters['address_2'] = $_REQUEST['address_2'];
						$changed_fields[] = "Address 2: '{$location->address_2}' to '{$_REQUEST['address_2']}'";
					}
					if (isset($_REQUEST['city']) && $_REQUEST['city'] != $location->city) {
						$parameters['city'] = $_REQUEST['city'];
						$changed_fields[] = "City: '{$location->city}' to '{$_REQUEST['city']}'";
					}
					if ($_REQUEST['province_id'] != $location->province_id) {
						$parameters['province_id'] = $_REQUEST['province_id'];
						$old_province = $location->province();
						$new_province = new \Geography\Province($_REQUEST['province_id']);
						$changed_fields[] = "Province: '{$old_province->name}' to '{$new_province->name}'";
					}
					if ($_REQUEST['zip_code'] != $location->zip_code) {
						$parameters['zip_code'] = $_REQUEST['zip_code'];
						$changed_fields[] = "Zip Code: '{$location->zip_code}' to '{$_REQUEST['zip_code']}'";
					}

					$location->update($parameters);
					if ($location->error())
						$page->addError("Error updating location " . $location->id . ": " . $location->error());
					elseif (!empty($changed_fields) && isset($_REQUEST['organization_id']) && isset($organization) && $organization->id) {
						// Log location update to organization audit log
						$audit_notes = "Location '{$location->name}' updated: " . implode("; ", $changed_fields);
						$organization->auditRecord('ORGANIZATION_UPDATED', $audit_notes);
					}
				} else {
					$parameters['name'] = $_REQUEST['name'];
					$parameters['address_1'] = $_REQUEST['address_1'];
					$parameters['address_2'] = $_REQUEST['address_2'];
					if (isset($_REQUEST['city']))
						$parameters['city'] = $_REQUEST['city'];
					$parameters['province_id'] = $_REQUEST['province_id'];
					$parameters['zip_code'] = $_REQUEST['zip_code'];
					app_log("Adding location " . $parameters['name'] . " in province " . $_REQUEST['province_id']);

					if (!$location->add($parameters)) {
						if ($location->error()) {
							$page->addError("Error adding location: " . $location->error());
						} else {
							$page->addError("Unhandled error adding location");
						}
					} elseif (isset($_REQUEST['organization_id']) && isset($organization) && $organization->id) {
						// Log location addition to organization audit log
						$audit_notes = "Location '{$parameters['name']}' added: {$parameters['address_1']}, {$parameters['city']}, {$parameters['zip_code']}";
						$organization->auditRecord('ORGANIZATION_UPDATED', $audit_notes);
					}
				}

				// apply any default billing or shipping set
				if (isset($_REQUEST['organization_id']) && isset($organization) && $organization->id) {
					$old_default_billing = $organization->default_billing_location_id;
					$old_default_shipping = $organization->default_shipping_location_id;
					$new_default_billing = isset($_REQUEST['default_billing']) ? $location->id : null;
					$new_default_shipping = isset($_REQUEST['default_shipping']) ? $location->id : null;
					
					$location->applyDefaultBillingAndShippingAddresses($_REQUEST['organization_id'], $location->id, isset($_REQUEST['default_billing']), isset($_REQUEST['default_shipping']));
					
					// Log default address changes to organization audit log
					$default_changes = array();
					if ($old_default_billing != $new_default_billing) {
						if ($new_default_billing == $location->id) {
							$default_changes[] = "Set '{$location->name}' as default billing address";
						} elseif ($old_default_billing == $location->id) {
							$default_changes[] = "Removed '{$location->name}' as default billing address";
						}
					}
					if ($old_default_shipping != $new_default_shipping) {
						if ($new_default_shipping == $location->id) {
							$default_changes[] = "Set '{$location->name}' as default shipping address";
						} elseif ($old_default_shipping == $location->id) {
							$default_changes[] = "Removed '{$location->name}' as default shipping address";
						}
					}
					if (!empty($default_changes)) {
						$organization->auditRecord('ORGANIZATION_UPDATED', implode("; ", $default_changes));
					}
				}
				if ($page->errorCount() < 1) {
					if (isset($_REQUEST['organization_id']) && !$location->associateOrganization($_REQUEST['organization_id']))
						$page->addError("Error associating organization: " . $location->error());
					if (isset($_REQUEST['user_id']) && !$location->associateUser($_REQUEST['user_id']))
						$page->addError("Error associating user: " . $location->error());
					if (!$page->errorCount()) {
						$page->success = "Changes saved";
						// Refresh organization object to get updated default location IDs
						if (isset($_REQUEST['organization_id'])) {
							$organization = new \Register\Organization($_REQUEST['organization_id']);
						}
					}
				}
			}
		}
	}
}

$world = new \Geography\World();
$countries = $world->countries();

$page->title = "Location Details";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
if (isset($organization->id)) {
	$page->addBreadcrumb($organization->name, "/_register/admin_organization?organization_id=".$organization->id);
}
$page->addBreadcrumb("Add/Edit Location", "");

$provinces = array();
if (isset($_REQUEST['id']) && $_REQUEST['id']) {
	$selected_province = $location->province();
	$selected_country = $selected_province->country();
	$provinces = $selected_country->provinces();
}
