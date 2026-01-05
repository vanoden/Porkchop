<?php
###################################################
## admin_account_search_tags_mc.php				###
## This program handles the search tags tab for	###
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

/** @section Search Tag Management
 * This section handles the management of search tags for the customer account.
 * It allows adding new tags, removing existing tags, and fetching all tags associated with the customer
 */
if (!empty($_REQUEST['newSearchTag']) && empty($_REQUEST['removeSearchTag'])) {

	$searchTag = new \Site\SearchTag();
	$searchTagList = new \Site\SearchTagList();
	$searchTagXref = new \Site\SearchTagXref();

	if (!empty($_REQUEST['newSearchTag']) && !empty($_REQUEST['newSearchTagCategory']) && $searchTag->validName($_REQUEST['newSearchTag']) && $searchTag->validName($_REQUEST['newSearchTagCategory'])) {

		// Check if the tag already exists (by both category and value)
		$existingTag = $searchTagList->find(array('class' => 'Register::Customer', 'category' => $_REQUEST['newSearchTagCategory'], 'value' => $_REQUEST['newSearchTag']));

		if (empty($existingTag)) {

			// Create a new tag
			$searchTag->add(array('class' => 'Register::Customer', 'category' => $_REQUEST['newSearchTagCategory'], 'value' => $_REQUEST['newSearchTag']));
			if ($searchTag->error()) {
				$page->addError("Error adding Register Customer search tag");
			} else {
				// Create a new cross-reference
				$searchTagXref->add(array('tag_id' => $searchTag->id, 'object_id' => $customer_id));
				if ($searchTagXref->error()) {
					$page->addError("Error adding Register Customer tag cross-reference: " . $searchTagXref->error());
				} else {
					$page->appendSuccess("Register Customer Search Tag added Successfully");
				}
			}
		} else {
			// Create a new cross-reference with the existing tag
			$searchTagXref->add(array('tag_id' => $existingTag[0]->id, 'object_id' => $customer_id));
			if ($searchTagXref->error()) {
				$page->addError("Error adding Register Customer tag cross-reference: " . $searchTagXref->error());
			} else {
				$page->appendSuccess("Register Customer Search Tag added Successfully");
			}
		}
	} else {
		$page->addError("Value for Register Customer Tag and Category are required");
	}
}

// remove tag from Register Customer
if (!empty($_REQUEST['removeSearchTagId'])) {
	$searchTagXrefItem = new \Site\SearchTagXref();
	$searchTagXrefItem->deleteTagForObject($_REQUEST['removeSearchTagId'], "Register::Customer", $customer_id);
	$page->appendSuccess("Register Customer Search Tag removed Successfully");
}

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

$page->title = "Customer Account Details - Search Tags";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);

// get unique categories and tags for autocomplete
$searchTagList = new \Site\SearchTagList();
$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();

// get tags for Register Customer
$searchTagXref = new \Site\SearchTagXrefList();
$searchTagXrefs = $searchTagXref->find(array("object_id" => $customer_id, "class" => "Register::Customer"));

$registerCustomerSearchTags = array();
foreach ($searchTagXrefs as $searchTagXrefItem) {
	$searchTag = new \Site\SearchTag();
	$searchTag->load($searchTagXrefItem->tag_id);
	$registerCustomerSearchTags[] = $searchTag;
}
