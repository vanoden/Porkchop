<?php
###################################################
## admin_account_roles_mc.php					###
## This program handles role assignments for		###
## customer account management.					###
## A. Caravello 11/12/2002						###
###################################################

$page = new \Site\Page(array("module" => 'register', "view" => 'account'));
$page->requirePrivilege('manage customers');
$page->setAdminMenuSection("Customer");  // Keep Customer section open
$customer = new \Register\Customer();

$site_config = new \Site\Configuration();
$site_config->get('website_images');
if (!empty($site_config->value)) {
	$repository = new \Storage\Repository();
	$repository->get($site_config->value);
	$repository = $repository->getInstance();
}

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
 * This section handles the form submission for applying role changes.
 * It validates the input and updates the customer's role assignments.
 */
if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply") {

	// Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
	if (!isset($_POST['csrfToken']) || !$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid request");
		return 403;
	} else {
		app_log("Roles form submitted", 'debug', __FILE__, __LINE__);
		
		// Process role assignments
		if ($customer_id) {
			app_log("Updating roles for customer " . $customer_id, 'debug', __FILE__, __LINE__);
			$customer = new \Register\Customer($customer_id);
			
			// Get all available roles
			$rolelist = new \Register\RoleList();
			$all_roles = $rolelist->find();
			
			// Process each role
			foreach ($all_roles as $role) {
				if (isset($_REQUEST['role'][$role->id])) {
					// Role is checked - add it if not already assigned
					if (!$customer->has_role($role->name)) {
						if (! $customer->add_role($role->id)) {
							app_log("Error adding role: " . $customer->error(), 'error', __FILE__, __LINE__);
							$page->addError("Error adding role &quot;".$role->name."&quot; to customer &quot;".$customer->full_name()."&quot;: ".$customer->error());
						}
						else {
							app_log("Added role: " . $role->name, 'debug', __FILE__, __LINE__);
							$page->appendSuccess("Added role &quot;".$role->name."&quot; to customer &quot;".$customer->full_name()."&quot;.");
						}
					}
				} else {
					// Role is not checked - remove it if assigned
					if ($customer->has_role($role->name)) {
						if (! $customer->drop_role($role->id)) {
							app_log("Error removing role: " . $customer->error(), 'error', __FILE__, __LINE__);
							$page->addError("Error removing role &quot;".$role->name."&quot; from customer &quot;".$customer->full_name()."&quot;: ".$customer->error());
							continue;
						}
						else {
							app_log("Removed role: " . $role->name, 'debug', __FILE__, __LINE__);
							$page->appendSuccess("Removed role &quot;".$role->name."&quot; from customer &quot;".$customer->full_name()."&quot;.");
						}
					}
				}
			}
			
			if ($customer->error()) {
				app_log("Error updating customer roles: " . $customer->error(), 'error', __FILE__, __LINE__);
				$page->addError("Error updating customer roles.  Our admins have been notified.  Please try again later");
				goto load;
			} else {
				$page->appendSuccess("Customer roles updated successfully.");
			}
		} else {
			$page->addError("Invalid customer ID");
		}
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

$page->title = "Customer Account Details";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);

// get customer queued status
$queuedCustomer = new \Register\Queue(); 
$queuedCustomer->getByQueuedLogin($customer->id);
if (!empty($queuedCustomer->status)) $registration_status = $queuedCustomer->status;
else $registration_status = "COMPLETE";

// get unique categories and tags for autocomplete
$searchTagList = new \Site\SearchTagList();
$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();

if (!isset($target)) $target = '';
?>