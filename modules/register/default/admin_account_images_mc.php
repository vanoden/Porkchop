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

// Load Repository for Image Uploads
$site_config = new \Site\Configuration();
$site_config->get('website_images');
$repositoryFactory = new \Storage\RepositoryFactory();
if (!empty($site_config->value)) {
	$repository = $repositoryFactory->createWithCode($site_config->value);
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

// Ensure customer is loaded before any operations
if (isset($customer_id) && (!$customer->id || $customer->id != $customer_id)) {
	$customer = new \Register\Customer($customer_id);
}

#######################################
## Handle Actions					###
#######################################

/** @section Image Management
 * This section handles the management of customer images.
 */
$image = new \Media\Image();
if (isset($_REQUEST['new_image_code']) && $_REQUEST['new_image_code']) {
	$image->get($_REQUEST['new_image_code']);
	if ($customer->id) {
		$customer->addImage($image->id, 'Register\Customer');
	}
}

if (isset($_REQUEST['deleteImage']) && $_REQUEST['deleteImage']) {
	$image->get($_REQUEST['deleteImage']);
	if ($customer->id) {
		$customer->dropImage($image->id, 'Register\Customer');
	}
}

if (isset($_REQUEST['updateImage']) && $_REQUEST['updateImage'] == 'true') {
	if ($customer->id) {
		$defaultImageId = $_REQUEST['default_image_id'];
		$customer->setMetadataScalar('default_image', $defaultImageId);
		if ($customer->error()) {
			$page->addError("Error setting default image: " . $customer->error());
		} else {
			$page->appendSuccess('Default image updated successfully.');
		}
	} else {
		$page->addError("Customer not found");
	}
}

/** @section File Upload
 * This section handles the file upload functionality for customer images.
 */
if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {
	if (!isset($_REQUEST['csrfToken']) || ! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
		$page->addError("Invalid Token");
	} else {
		$page->requirePrivilege('upload storage files');
		
		// Check if repository is configured
		if (empty($repository) || !isset($repository->id)) {
			$page->addError("Repository not configured for image uploads");
		}
		// Check if file was uploaded
		elseif (!isset($_FILES['uploadFile']) || empty($_FILES['uploadFile']['tmp_name']) || !is_uploaded_file($_FILES['uploadFile']['tmp_name'])) {
			$page->addError("No file was selected or file upload failed");
		}
		// Check for upload errors
		elseif (isset($_FILES['uploadFile']['error']) && $_FILES['uploadFile']['error'] !== UPLOAD_ERR_OK) {
			$errorMessages = array(
				UPLOAD_ERR_INI_SIZE => "File exceeds upload_max_filesize",
				UPLOAD_ERR_FORM_SIZE => "File exceeds MAX_FILE_SIZE",
				UPLOAD_ERR_PARTIAL => "File was only partially uploaded",
				UPLOAD_ERR_NO_FILE => "No file was uploaded",
				UPLOAD_ERR_NO_TMP_DIR => "Missing temporary folder",
				UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk",
				UPLOAD_ERR_EXTENSION => "File upload stopped by extension"
			);
			$errorMsg = $errorMessages[$_FILES['uploadFile']['error']] ?? "Unknown upload error";
			$page->addError("File upload error: " . $errorMsg);
		}
		// Ensure customer is loaded
		elseif (!$customer->id) {
			$page->addError("Customer not found");
		} else {
			$imageUploaded = $customer->uploadImage($_FILES['uploadFile'], $repository->id, 'spectros_user_image', 'Register\Customer');
			if ($imageUploaded) {
				$page->success = "File uploaded";
			} else {
				$page->addError("Error uploading file: " . $customer->error());
			}
		}
	}
}

$customerImages = $customer->images('Register\Customer');
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
