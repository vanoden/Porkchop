<?php
###################################################
## admin_account_roles_mc.php					###
## This program handles the roles tab for		###
## customer account management.				###
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

/** @section Apply Changes
 * This section handles the form submission for applying changes to the customer account.
 * It validates the input, updates the customer information, and handles role assignments.
 */
if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply") {

	// Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid request");
		return 403;
	} else {
		app_log("Account form submitted", 'debug', __FILE__, __LINE__);
		$parameters = array();
		if (!$customer->validLogin($_REQUEST['login']))
			$page->addError("Invalid login");
		elseif (!$customer->validStatus($_REQUEST['status']))
			$page->addError("Invalid status " . $_REQUEST['status']);
		else {

			$parameters['login'] = $_REQUEST["login"];
			if (isset($_REQUEST["first_name"]) && preg_match('/^[\w\-\.\_\s]+$/', $_REQUEST["first_name"])) $parameters['first_name'] = $_REQUEST["first_name"];
			if (isset($_REQUEST["last_name"]) && preg_match('/^[\w\-\.\_\s]+$/', $_REQUEST["last_name"])) $parameters['last_name'] = $_REQUEST["last_name"];
			if (isset($_REQUEST["timezone"])) $parameters['timezone'] = $_REQUEST["timezone"];
			if (isset($_REQUEST["status"])) $parameters['status'] = $_REQUEST["status"];
			
			if (isset($_REQUEST["automation"])) {
				if ($_REQUEST['automation'])
					$parameters['automation'] = true;
				else
					$parameters['automation'] = false;
			}

			if (isset($_REQUEST['organization_id'])) $parameters["organization_id"] = $_REQUEST["organization_id"];

			// time_based_password required or not
			$parameters['time_based_password'] = 0;
			if (isset($_REQUEST["time_based_password"]) && !empty($_REQUEST["time_based_password"])) $parameters['time_based_password'] = 1;

			// profile visibility
			if (isset($_REQUEST["profile"])) $parameters["profile"] = $_REQUEST["profile"];

			if ($customer_id) {
				app_log("Updating customer " . $customer_id, 'debug', __FILE__, __LINE__);
				$customer = new \Register\Customer($customer_id);
				$customer->update($parameters);

				// set the job title and description
				$customer->setMetadataScalar('job_title', $_REQUEST['job_title']);
				$customer->setMetadataScalar('job_description', $_REQUEST['job_description']);

				if ($customer->error()) {
					app_log("Error updating customer: " . $customer->error(), 'error', __FILE__, __LINE__);
					$page->addError("Error updating customer information.  Our admins have been notified.  Please try again later");
					goto load;
				}
			}
		}

		// Get List Of Possible Roles
		app_log("Checking roles", 'notice', __FILE__, __LINE__);
		$rolelist = new \Register\RoleList();
		$available_roles = $rolelist->find();
		app_log("Found " . $rolelist->count() . " roles", 'trace', __FILE__, __LINE__);

		// Loop through all roles and apply changes if necessary
		foreach ($available_roles as $role) {
			app_log("Checking role " . $role->name . "[" . $role->id . "]", 'trace', __FILE__, __LINE__);
			if (isset($_REQUEST['role'][$role->id]) && $_REQUEST['role'][$role->id]) {
				app_log("Role is selected", 'trace', __FILE__, __LINE__);
				if (!$customer->has_role($role->name)) {
					app_log("Adding role " . $role->name . " for " . $customer->code, 'debug', __FILE__, __LINE__);
					$customer->add_role($role->id);
				}
			} else {
				app_log("Role is not selected", 'trace', __FILE__, __LINE__);
				if ($customer->has_role($role->name)) {
					app_log("Role " . $role->name . " being revoked from " . $customer->code, 'debug', __FILE__, __LINE__);
					$customer->drop_role($role->id);
				}
			}
		}
		$page->appendSuccess('Your changes have been saved');
	}
}

load:
if ($customer_id) {
	$customer = new \Register\Customer($customer_id);
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

$page->title = "Customer Account Details - Assigned Roles";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);
