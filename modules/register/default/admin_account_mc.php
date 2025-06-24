<?php
###################################################
## register_mc.php								###
## This program collects registration info		###
## for the user.								###
## A. Caravello 11/12/2002						###
###################################################

$page = new \Site\Page(array("module" => 'register', "view" => 'account'));
$page->requirePrivilege('manage customers');
$customer = new \Register\Customer();

$factory = new \Storage\RepositoryFactory();
$repository = new \Storage\Repository();
$site_config = new \Site\Configuration();
$site_config->get('website_images');
if (!empty($site_config->value)) $repository = $factory->get($site_config->value);

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
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid request");
		return 403;
	} else {
		$_contact = new \Register\Contact($_REQUEST['register-contacts-id']);
		$_contact->delete();
		$_contact->auditRecord('USER_UPDATED', 'Contact Entry ' . $_contact->type . ' ' . $_contact->value . ' ' . $_contact->notes . ' ' . $_contact->description . ' has been removed.', $customer_id);
		$page->success = 'Contact Entry ' . $_REQUEST['register-contacts-id'] . ' has been removed.';
	}
}

// handle send another verification email
if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Resend Email") {

	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
		$page->addError("Invalid Request");
	} else {

		$validation_key = md5(microtime());
		$customer = new \Register\Customer($customer_id);
		$customer->update(array('validation_key'=>$validation_key));

		// create the verify account email
		$verify_url = $GLOBALS['_config']->site->hostname . '/_register/new_customer?method=verify&access=' . $validation_key . '&login=' . $customer->code;
		if ($GLOBALS['_config']->site->https) $verify_url = "https://$verify_url";
		else $verify_url = "http://$verify_url";

		$template = new \Content\Template\Shell(
			array(
				'path'	=> $GLOBALS['_config']->register->verify_email->template,
				'parameters'	=> array(
					'VERIFYING.URL' => $verify_url
				)
			)
		);
		if ($template->error()) {
			app_log($template->error(),'error');
			$page->addError("Error generating verification email, please contact us at ".$GLOBALS['_config']->site->support_email." to complete your registration, thank you!");
		}
		else {
			$message = new \Email\Message($GLOBALS['_config']->register->verify_email);
			$message->html(true);
			$message->body($template->output());
			if (! $customer->notify($message)) {
				$page->addError("Confirmation email could not be sent, please contact us at ".$GLOBALS['_config']->site->support_email." to complete your registration, thank you!");
				app_log("Error sending confirmation email: ".$customer->error(),'error');
			} else {
				$page->success = "Another verification email has been issued.";
			}
		}
	}
}

// handle form "apply" submit
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

			if (isset($_REQUEST["password"]) and ($_REQUEST["password_2"])) {
				if ($_REQUEST["password"] != $_REQUEST["password_2"]) {
					$page->addError("Passwords do not match");
					goto load;
				}
			}

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
				if ($_REQUEST['password']) {
					if (!$customer->changePassword($_REQUEST["password"])) {
						$page->addError("Password needs more complexity");
					} else {
						$page->appendSuccess("Password changed successfully.");
					}
				}

			} else {

				app_log("New customer registration", 'debug', __FILE__, __LINE__);

				// Default Login to Email Address
				if (!$_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['email_address'];

				// Generate Validation Key
				$validation_key = md5(microtime());
				$parameters["login"] = $_REQUEST['login'];

				###########################################
				## Add User To Database
				###########################################

				// Add Customer Record to Database
				$customer = new \Register\Customer();
				$customer->add($parameters);

				if ($customer->error()) {
					$page->addError($customer->error());
					goto load;
				}

				if ($customer->id) {
					$GLOBALS['_SESSION_']->update(array("user_id" => $customer->id));
					if ($GLOBALS['_SESSION_']->error) {
						$page->addError("Error updating session: " . $GLOBALS['_SESSION_']->error);
						goto load;
					}
				}

				if (empty($_REQUEST['password'])) $_REQUEST['password'] = $customer->randomPassword();
				$customer->changePassword($_REQUEST['password']);

				$template = new \Content\Template\Shell($GLOBALS['_config']->register->account_created->template);
				$template->addParam('URL', $GLOBALS['_config']->site->url);
				$template->addParam('WEBSITE', $GLOBALS['_config']->site->hostname);
				$template->addParam('LOGIN', $_REQUEST['login']);
				$template->addParam('PASSWORD', $_REQUEST['password']);

				$message = new \Email\Message();
				$message->from($GLOBALS['_config']->register->confirmation->from);
				$message->subject($GLOBALS['_config']->register->confirmation->subject);
				$message->body($template->output());

				// Registration Confirmation
				$customer->notify($message);
				if ($customer->error()) {
					app_log("Error sending registration confirmation: " . $_contact->error(), 'error', __FILE__, __LINE__);
					$page->addError("Sorry, we were unable to complete your registration");
					goto load;
				}

				// Redirect to Address Page If Order Started
				if (isset($target))
					$next_page = $target;
				elseif (isset($order_id))
					$next_page = "/_cart/address";
				else
					$next_page = "/_register/thank_you";
				header("Location: $next_page");
			}
		}

		// Process Contact Entries
		app_log("Processing contact entries", 'debug', __FILE__, __LINE__);
		foreach ($_REQUEST['type'] as $contact_id => $type) {

			if (!$_REQUEST['type'][$contact_id]) continue;
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
				app_log("Adding contact record", 'debug', __FILE__, __LINE__);
				if (isset($_REQUEST['notify'][0]))
					$notify = true;
				else
					$notify = false;

				if (isset($_REQUEST['public'][0]))
					$public = true;
				else
					$public = false;

				$contact = new \Register\Contact($contact_id);
				if ($contact->error()) {
					$page->addError($contact->error());
				} else {
					// Create Contact Record
					if (!$contact->validType($_REQUEST['type'][0]))
						$page->addError("Invalid contact type");
					elseif (!$contact->validValue($_REQUEST['type'][0], $_REQUEST['value'][0]))
						$page->addError("Invalid value for contact type");
					else {
						$contactRecord = array(
							"person_id" => $customer_id,
							"type" => $_REQUEST['type'][0],
							"description" => noXSS(trim($_REQUEST['description'][0])),
							"value" => $_REQUEST['value'][0],
							"notes" => noXSS(trim($_REQUEST['notes'][0])),
							"notify" => $notify,
							"public" => $public
						);
						$customer->addContact($contactRecord);
						$contact->auditRecord("USER_UPDATED","Customer Contact added: " . implode(", ", $contactRecord), $customer_id);
						if ($customer->error()) {
							$page->addError("Error adding contact: " . $customer->error());
							goto load;
						}
					}
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

if (isset($_REQUEST['customer_id'])) $customer = new \Register\Customer($_REQUEST['customer_id']);

$image = new \Media\Image();
if ($_REQUEST['new_image_code']) {
	$image->get($_REQUEST['new_image_code']);
	$customer->addImage($image->id, 'Register\Customer');
}

if ($_REQUEST['deleteImage']) {
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
				
// File Upload Form Submitted
if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
		$page->addError("Invalid Token");
	} else {
		$page->requirePrivilege('upload storage files');
		
		$imageUploaded = $customer->uploadImage($_FILES['uploadFile'], '', 'spectros_user_image', $_REQUEST['repository_id'], 'Register\Customer');
		if ($imageUploaded) {
			$page->success = "File uploaded";
		} else {
			$page->addError("Error uploading file: " . $customer->error());
		}
	}
}  
$customerImages = $customer->images('Register\Customer');

if (isset($_REQUEST["btnResetFailures"])) {

	// Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
	if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid request");
		return 403;
	} else {
		$customer = new \Register\Customer($_REQUEST['customer_id']);
		$customer->resetAuthFailures();
	}
}

// add tag to Register Customer
if (!empty($_REQUEST['newSearchTag']) && empty($_REQUEST['removeSearchTag'])) {

	$searchTag = new \Site\SearchTag();
	$searchTagList = new \Site\SearchTagList();
	$searchTagXref = new \Site\SearchTagXref();

	if (!empty($_REQUEST['newSearchTag']) && !empty($_REQUEST['newSearchTagCategory']) && $searchTag->validName($_REQUEST['newSearchTag']) && $searchTag->validName($_REQUEST['newSearchTagCategory'])) {

		// Check if the tag already exists
		$existingTag = $searchTagList->find(array('class' => 'Register::Customer', 'value' => $_REQUEST['newSearchTag']));

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

// get tags for Register Customer
$searchTagXref = new \Site\SearchTagXrefList();
$searchTagXrefs = $searchTagXref->find(array("object_id" => $customer_id, "class" => "Register::Customer"));

$registerCustomerSearchTags = array();
foreach ($searchTagXrefs as $searchTagXrefItem) {
	$searchTag = new \Site\SearchTag();
	$searchTag->load($searchTagXrefItem->tag_id);
	$registerCustomerSearchTags[] = $searchTag;
}

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

if (!empty($customer->code)) {
	$authFailureList = new \Register\AuthFailureList();
	$authFailures = $authFailureList->find(array('_limit' => 5, 'login' => $customer->code));
} else {
	$authFailures = array();
}

if (!isset($target)) $target = '';

$page->title = "Customer Account Details";
$page->addBreadcrumb("Organizations", "/_register/organizations");
$organization = $customer->organization();
if (isset($organization->id)) $page->addBreadcrumb($organization->name, "/_register/organization?id=" . $organization->id);
if (isset($customer->id)) $page->addBreadcrumb($customer->full_name(), "/_register/admin_account?customer_id=" . $customer->id);

// terms of use user history list
$termsOfUseList = new \Site\TermsOfUseList();
$terms = $termsOfUseList->find();
$termsOfUseActionList = new \Site\TermsOfUseActionList();

// Get List of Locations
$locations = $customer->locations();

// get customer queued status
$queuedCustomer = new \Register\Queue(); 
$queuedCustomer->getByQueuedLogin($customer->id);

// get unique categories and tags for autocomplete
$searchTagList = new \Site\SearchTagList();
$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();
