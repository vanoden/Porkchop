<?php
###################################################
## admin_account_images_mc.php					###
## This program handles the images tab for		###
## customer account management.				###
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

/** @section Image Management
 * This section handles the management of customer images.
 */
$image = new \Media\Image();
if (isset($_REQUEST['new_image_code']) && $_REQUEST['new_image_code']) {
	$image->get($_REQUEST['new_image_code']);
	$customer->addImage($image->id, 'Register\Customer');
}

if (isset($_REQUEST['deleteImage']) && $_REQUEST['deleteImage']) {
	$image->get($_REQUEST['deleteImage']);
	$customer->dropImage($image->id, 'Register\Customer');
}

if (isset($_REQUEST['updateImage']) && $_REQUEST['updateImage'] == 'true') {
	$defaultImageId = $_REQUEST['default_image_id'];
	$customer->setMetadataScalar('default_image', $defaultImageId);
	if ($customer->error()) {
		$page->addError("Error setting default image: " . $customer->error());
	} else {
		$page->appendSuccess('Default image updated successfully.', 'success');
	}
}

/** @section File Upload
 * This section handles the file upload functionality for customer images.
 */
if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
		$page->addError("Invalid Token");
	} else {
		$page->requirePrivilege('upload storage files');
		
		$imageUploaded = $customer->uploadImage($_FILES['uploadFile'], $repository->id, 'spectros_user_image', 'Register\Customer');
		if ($imageUploaded) {
			$page->success = "File uploaded";
		} else {
			$page->addError("Error uploading file: " . $customer->error());
		}
	}
}  
$customerImages = $customer->images('Register\Customer');

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

$page->title = "Customer Account Details - User Images";
$page->addBreadcrumb("Customer");
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/admin_organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);
