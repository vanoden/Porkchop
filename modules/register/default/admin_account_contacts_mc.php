<?php
###################################################
## admin_account_contacts_mc.php				###
## This program handles the contacts tab for	###
## customer account management.					###
## A. Caravello 11/12/2002						###
###################################################

$page = new \Site\Page(array("module" => 'register', "view" => 'account'));
$page->requirePrivilege('manage customers');
$page->setAdminMenuSection("Customer");  // Keep Customer section open
$customer = new \Register\Customer();

if (isset($_REQUEST['customer_id']) && preg_match('/^\d+$/', $_REQUEST['customer_id']))
	$customer_id = $_REQUEST['customer_id'];
elseif (preg_match('/^[\w\-\.\_]+$/', $GLOBALS['_REQUEST_']->query_vars_array[0])) {
	$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
	$customer->get($code);
	if ($customer->id)
		$customer_id = $customer->id;
	else
		$page->addError("Customer not found");
} else
	$customer_id = $GLOBALS['_SESSION_']->customer->id;

app_log($GLOBALS['_SESSION_']->customer->code . " accessing account of customer " . $customer_id, 'notice', __FILE__, __LINE__);

#######################################
## Handle Actions					###
#######################################

// handle form "delete" submit
if (isset($_REQUEST['submit-type']) && $_REQUEST['submit-type'] == "delete-contact") {
	// Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
	if (!isset($_POST['csrfToken']) || !$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid request");
		return 403;
	} else {
		if (isset($_REQUEST['register-contacts-id'])) {
			$_contact = new \Register\Contact($_REQUEST['register-contacts-id']);
			$_contact->delete();
			$_contact->auditRecord('USER_UPDATED', 'Contact Entry ' . $_contact->type . ' ' . $_contact->value . ' ' . $_contact->notes . ' ' . $_contact->description . ' has been removed.', $customer_id);
			$page->success = 'Contact Entry ' . $_REQUEST['register-contacts-id'] . ' has been removed.';
		}
	}
}

/** @section Apply Changes
 * This section handles the form submission for applying changes to the customer account.
 * It validates the input, updates the customer information, and handles contact entries.
 */
if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply") {

	// Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
	if (!isset($_POST['csrfToken']) || !$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid request");
		return 403;
	} else {
		app_log("Contact form submitted", 'debug', __FILE__, __LINE__);

		// Process Contact Entries
		app_log("Processing contact entries", 'debug', __FILE__, __LINE__);
		if (isset($_REQUEST['type'])) {
			foreach ($_REQUEST['type'] as $contact_id => $type) {
				app_log("Processing contact ID: $contact_id, type: $type", 'debug', __FILE__, __LINE__);

			if (!$_REQUEST['type'][$contact_id] || $_REQUEST['type'][$contact_id] == "0") continue;
			if ($contact_id > 0) {
				app_log("Updating contact record", 'debug', __FILE__, __LINE__);
				$contact = new \Register\Contact($contact_id);
				if ($contact->error()) {
					$page->addError($contact->error());
				} else {
					if (isset($_REQUEST['notify'][$contact_id]))
						$notify = true;
					else
						$notify = false;

					if (isset($_REQUEST['public'][$contact_id]))
						$public = true;
					else
						$public = false;

					if (!$contact->validType($_REQUEST['type'][$contact_id]))
						$page->addError("Invalid contact type");
					elseif (!$contact->validValue($_REQUEST['type'][$contact_id], $_REQUEST['value'][$contact_id]))
						$page->addError("Invalid value for added contact type: " . $_REQUEST['type'][$contact_id]);
					else {

						// Update Existing Contact Record
						$contactRecord = array(
							"type" => $_REQUEST['type'][$contact_id],
							"description" => noXSS(trim($_REQUEST['description'][$contact_id])),
							"value" => $_REQUEST['value'][$contact_id],
							"notes" => noXSS(trim($_REQUEST['notes'][$contact_id])),
							"notify" => $notify,
							"public" => $public
						);
						
						$noChanges = (
							$contact->type == $contactRecord['type'] &&
							$contact->description == $contactRecord['description'] &&
							$contact->value == $contactRecord['value'] &&
							$contact->notes == $contactRecord['notes'] &&
							$contact->notify == $contactRecord['notify'] &&
							$contact->public == $contactRecord['public']
						);
						if (!$noChanges) $contact->auditRecord("USER_UPDATED","Customer Contact updated: " . implode(", ", $contactRecord), $customer_id);	

						$contact->update($contactRecord);
						if ($contact->error()) {
							$page->addError("Error updating contact: " . $customer->error());
							goto load;
						}
					}
				}
			} else {
				app_log("Processing new contact (ID 0)", 'debug', __FILE__, __LINE__);
				// Only add new contact if a valid type is selected (not "0" or empty)
				if (isset($_REQUEST['type'][0]) && $_REQUEST['type'][0] != "0" && !empty($_REQUEST['type'][0])) {
					app_log("Valid contact type selected: " . $_REQUEST['type'][0], 'debug', __FILE__, __LINE__);
					app_log("Contact value: " . (isset($_REQUEST['value'][0]) ? $_REQUEST['value'][0] : 'NOT SET'), 'debug', __FILE__, __LINE__);
					app_log("Contact description: " . (isset($_REQUEST['description'][0]) ? $_REQUEST['description'][0] : 'NOT SET'), 'debug', __FILE__, __LINE__);
					app_log("Adding contact record", 'debug', __FILE__, __LINE__);
					if (isset($_REQUEST['notify'][0]))
						$notify = true;
					else
						$notify = false;

					if (isset($_REQUEST['public'][0]))
						$public = true;
					else
						$public = false;

					// Create Contact Record
					$temp_contact = new \Register\Contact();
					if (!isset($_REQUEST['type'][0]) || !$_REQUEST['type'][0])
						$page->addError("Invalid contact type");
					elseif (!$temp_contact->validType($_REQUEST['type'][0]))
						$page->addError("Invalid contact type");
					elseif (!isset($_REQUEST['value'][0]) || !$_REQUEST['value'][0])
						$page->addError("Contact value is required");
					elseif (!$temp_contact->validValue($_REQUEST['type'][0], $_REQUEST['value'][0]))
						$page->addError("Invalid value for contact type");
					else {
						$contactRecord = array(
							"person_id" => $customer_id,
							"type" => $_REQUEST['type'][0],
							"description" => isset($_REQUEST['description'][0]) ? noXSS(trim($_REQUEST['description'][0])) : '',
							"value" => $_REQUEST['value'][0],
							"notes" => isset($_REQUEST['notes'][0]) ? noXSS(trim($_REQUEST['notes'][0])) : '',
							"notify" => $notify,
							"public" => $public
						);
						$customer->addContact($contactRecord);
						if ($customer->error()) {
							$page->addError("Error adding contact: " . $customer->error());
							goto load;
						} else {
							app_log("Contact added successfully", 'debug', __FILE__, __LINE__);
						}
					}
				}
			}
		}
		}
		$page->appendSuccess('Your changes have been saved');
	}
}

load:
if ($customer_id) {
	$customer = new \Register\Customer($customer_id);
	$contacts = $customer->contacts();
}
$rolelist = new \Register\RoleList();
$all_roles = $rolelist->find();
$_department = new \Register\Department();
$departments = $_department->find();
app_log("Loading Organizations", 'trace', __FILE__, __LINE__);
$organizationlist = new \Register\OrganizationList();
$organizations = $organizationlist->find();
$_contact = new \Register\Contact();
$contact_types = $_contact->types;

if (!isset($target)) $target = '';

$page->title = "Customer Account Details - Methods of Contact";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);
