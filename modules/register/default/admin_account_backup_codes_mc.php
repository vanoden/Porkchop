<?php
###################################################
## admin_account_backup_codes_mc.php			###
## This program handles the backup codes tab	###
## for customer account management.			###
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

if (isset($_REQUEST['generate_backup_codes'])) {
    // Only process if the Generate Backup Codes button was clicked (not the Apply button)
    if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        $page->addError("Invalid request");
    } else {
        $customer = new \Register\Customer($customer_id);
        // Remove all existing backup codes
        $customer->deleteAllBackupCodes();
        // Generate 6 new codes
        $generatedBackupCodes = $customer->generateBackupCodes(6);
        if (!$generatedBackupCodes) {
            if ($customer->error()) {
                $page->addError($customer->error());
            } else {
                $page->addError("Failed to generate backup codes.");
            }
        } else {
            $page->appendSuccess("Backup codes generated successfully.");
        }
    }
}

load:
if ($customer_id) {
	$customer = new \Register\Customer($customer_id);
	$contacts = $customer->contacts();
	// Fetch all backup codes for this user using the customer object
	if (method_exists($customer, 'getAllBackupCodes')) {
		$allBackupCodes = $customer->getAllBackupCodes();
	} else {
		$allBackupCodes = array();
	}
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

$page->title = "Customer Account Details - Backup Codes";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);
