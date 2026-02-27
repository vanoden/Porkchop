<?php
###################################################
## admin_account_locations_mc.php				###
## This program handles the locations tab for	###
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

$page->title = "Customer Account Details - Locations";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);

// Process Hide/Unhide before loading list (so list shows current state)
if ($customer->id && (isset($_REQUEST['setHidden']) || isset($_REQUEST['setVisible'])) && is_numeric($_REQUEST['setHidden'] ?? $_REQUEST['setVisible'] ?? 0)) {
	$loc_id = (int)($_REQUEST['setHidden'] ?? $_REQUEST['setVisible']);
	$loc = new \Register\Location($loc_id);
	if ($loc->id) {
		$belongs_to_customer = ($organization->id && $loc->belongsToOrganization($organization->id)) || $loc->belongsToUser($customer->id);
		if ($belongs_to_customer) {
			$hidden = isset($_REQUEST['setHidden']) ? 1 : 0;
			if ($loc->update(array('hidden' => $hidden)))
				$page->appendSuccess($hidden ? "Address hidden." : "Address visible again.");
			else
				$page->addError("Could not update address.");
		} else {
			$page->addError("Address does not belong to this customer.");
		}
	} else {
		$page->addError("Address not found.");
	}
}

// Location IDs that belong to the customer's organization (show company name next to these)
$org_location_ids = array();
if ($customer->id && isset($organization->id) && $organization->id) {
	$locHelper = new \Register\Location(0);
	$org_location_ids = $locHelper->locationIdsForOrganization($organization->id);
}

// Get List of Locations (exclude hidden when show_hidden not set)
$show_hidden = !empty($_REQUEST['show_hidden']);
$locations = $customer->locations(array('include_hidden' => $show_hidden));
