<?php
###################################################
### account_mc.php								###
### This program collects registration info		###
### for the user.								###
### A. Caravello 10/23/2024						###
###################################################
$site = new \Site();
$page = $site->page();
$page->requireAuth();

// Check if a customer_id is provided
if (isset($_REQUEST['customer_id']) && preg_match('/^\d+$/', $_REQUEST['customer_id'])) {
	$customer = new \Register\Customer($_REQUEST['customer_id']);
} elseif ($GLOBALS['_SESSION_']->customer->id) {
	$customer = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
} else {
	// If no customer_id is provided, redirect to login
	header("location: /_register/login?target=_register/account");
	exit;
}

// Check if the logged-in user is in the same organization as the customer
$canView = true;
if ($GLOBALS['_SESSION_']->customer->organization_id != $customer->organization_id) {
	$page->addError("You do not have permission to view this customer's account.");
	$canView = false;
}

// Check if the logged-in user is the same as the customer being viewed
if ($GLOBALS['_SESSION_']->customer->id != $customer->id && $canView) {
	$readOnly = true;
	$page->appendSuccess("You are currrently viewing another user in your organization");
} else {
	$readOnly = false;
}

app_log($GLOBALS['_SESSION_']->customer->code . " accessing account of customer " . $customer->id, 'notice', __FILE__, __LINE__);

#######################################
### Handle Actions					###
#######################################

$repository = new \Storage\Repository();
$site_config = new \Site\Configuration();
$site_config->get('website_images');
if (!empty($site_config->value)) {
	$repository->get($site_config->value);
	$repository = $repository->getInstance();
}

$image = new \Media\Image();
if (!empty($_REQUEST['new_image_code'])) {
	$image->get($_REQUEST['new_image_code']);
	$customer->addImage($image->id, 'Register\Customer');
}

if (!empty($_REQUEST['deleteImage'])) {
	$image->get($_REQUEST['deleteImage']);
	$customer->dropImage($image->id, 'Register\Customer');
}

if (isset($_REQUEST['updateImage']) && $_REQUEST['updateImage'] == 'true') {
			$defaultImageId = $_REQUEST['default_image_id'] ?? '';
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

		$imageUploaded = $customer->uploadImage($_FILES['uploadFile'], '', 'spectros_product_image', $_REQUEST['repository_id'] ?? '', 'Register\Customer');
		if ($imageUploaded) {
			$page->success = "File uploaded";
		} else {
			$page->addError("Error uploading file: " . $customer->error());
		}
	}
}

$customerImages = $customer->images('Register\Customer');

// handle form "delete" submit
if (isset($_REQUEST['submit-type']) && $_REQUEST['submit-type'] == "delete-contact" && !$readOnly) {
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid Request");
	} else {
		$contact = new \Register\Contact($_REQUEST['register-contacts-id'] ?? 0);
		$contact->delete();
		$page->success = 'Contact Entry ' . ($_REQUEST['register-contacts-id'] ?? 0) . ' has been removed.';
		$contact->auditRecord('USER_UPDATED', 'Contact Entry ' . $contact->type . ' ' . $contact->value . ' ' . $contact->notes . ' ' . $contact->description . ' has been removed.');
	}
}

// handle form "apply" submit
if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply" && !$readOnly) {
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid Request");
	} else {
		app_log("Account form submitted", 'debug', __FILE__, __LINE__);
		$parameters = array();

		if (! validTimezone($_REQUEST['timezone'] ?? '')) $_REQUEST['timezone'] = 'America/New_York';

		if (isset($_REQUEST["first_name"])) 	$parameters['first_name']	= noXSS($_REQUEST["first_name"]);
		if (isset($_REQUEST["last_name"]))		$parameters['last_name']	= noXSS($_REQUEST["last_name"]);
		if (isset($_REQUEST["timezone"]))		$parameters['timezone']		= $_REQUEST["timezone"];
		if (isset($_REQUEST["password"]) and ($_REQUEST["password"])) {
			if ($_REQUEST["password"] != ($_REQUEST["password_2"] ?? '')) {
				$page->addError("Passwords do not match");
				goto load;
			} else
				$parameters["password"] = $_REQUEST["password"];
		}
		if (isset($_REQUEST["profile"])) $parameters["profile"] = $_REQUEST["profile"];

		// time_based_password required or not
		$parameters['time_based_password'] = 0;
		if (isset($_REQUEST["time_based_password"]) && !empty($_REQUEST["time_based_password"])) $parameters['time_based_password'] = 1;

		if ($customer->id) {

			app_log("Updating customer " . $customer->id, 'debug', __FILE__, __LINE__);
			$customer->update($parameters);

			// set the user to active if they're expired, this will ensure then can continue to login
			if (isset($parameters["password"])) {
				if ($customer->status == 'EXPIRED') $customer->update(array('status' => 'ACTIVE'));
				
				// Send password reset notification email when password is changed
				if ($customer && $customer->id) {
					$to_email = $customer->notify_email();
					if (!empty($to_email) && isset($GLOBALS['_config']->register->password_reset_notification)) {
						$email_config = $GLOBALS['_config']->register->password_reset_notification;
						if (isset($email_config->template) && file_exists($email_config->template)) {
							$template = new \Content\Template\Shell(
								array(
									'path' => $email_config->template,
									'parameters' => array(
										'CUSTOMER.FIRST_NAME' => $customer->first_name,
										'CUSTOMER.LOGIN' => $customer->code,
										'RESET.DATE' => date('Y-m-d'),
										'RESET.TIME' => date('H:i:s T'),
										'SUPPORT.EMAIL' => $GLOBALS['_config']->site->support_email,
										'SUPPORT.PHONE' => '1-800-SPECTROS',
										'LOGIN.URL' => 'http' . ($GLOBALS['_config']->site->https ? 's' : '') . '://' . $GLOBALS['_config']->site->hostname . '/_register/login',
										'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? 'Spectros Instruments'
									)
								)
							);

							if (!$template->error()) {
								$message = new \Email\Message();
								$message->html(true);
								$message->to($to_email);
								$message->from($email_config->from);
								$message->subject($email_config->subject);
								$message->body($template->output());

								$transportFactory = new \Email\Transport();
								$transport = $transportFactory->Create(array('provider' => $GLOBALS['_config']->email->provider));
								if ($transport && !$transport->error()) {
									$transport->hostname($GLOBALS['_config']->email->hostname);
									$transport->token($GLOBALS['_config']->email->token);
									if (!$transport->deliver($message)) {
										app_log("Error sending password reset notification email: " . $transport->error(), 'error', __FILE__, __LINE__);
									} else {
										app_log("Password reset notification email sent to " . $to_email, 'info', __FILE__, __LINE__);
										$customer->auditRecord('PASSWORD_RESET_NOTIFICATION_SENT', 'Password reset notification email sent to: ' . $to_email);
									}
								} else {
									app_log("Error creating email transport for password reset notification: " . ($transport ? $transport->error() : 'Transport creation failed'), 'error', __FILE__, __LINE__);
								}
							} else {
								app_log("Error generating password reset notification email: " . $template->error(), 'error', __FILE__, __LINE__);
							}
						} else {
							app_log("Password reset notification email template not found", 'error', __FILE__, __LINE__);
						}
					} else {
						if (empty($to_email)) {
							app_log("No email address available for customer " . $customer->id, 'error', __FILE__, __LINE__);
						} else {
							app_log("Password reset notification email configuration not found", 'error', __FILE__, __LINE__);
						}
					}
				} else {
					app_log("Invalid customer object for password reset notification", 'error', __FILE__, __LINE__);
				}
			}
			if (isset($parameters["profile"])) $customer->update(array('profile' => $parameters["profile"]));
			
			$customer->setMetadataScalar('job_title', (string)($_REQUEST['job_title'] ?? ''));
			$customer->setMetadataScalar('job_description', (string)($_REQUEST['job_description'] ?? ''));

			if ($customer->error()) {
				app_log("Error updating customer: " . $customer->error(), 'error', __FILE__, __LINE__);
				$page->addError("Error updating customer information.  Our admins have been notified.  Please try again later");
				goto load;
			}
		} else {

			### THIS NEVER HAPPENS ###
			app_log("New customer registration", 'debug', __FILE__, __LINE__);

			# Default Login to Email Address
			if (! ($_REQUEST['login'] ?? '')) $_REQUEST['login'] = $_REQUEST['email_address'] ?? '';

			# Generate Validation Key
			$validation_key = md5(microtime());

			$parameters["login"] = $_REQUEST['login'] ?? '';

			###################################################
			### Add Customer Record to Database				###
			###################################################
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

			// Registration Confirmation
			$_contact = new \Register\Contact();
			$_contact->notify(
				array(
					"from"		=> $GLOBALS['_config']->register->confirmation->from,
					"subject"	=> $GLOBALS['_config']->register->confirmation->subject,
					"message"	=> "Thank you for registering",
				)
			);
			if ($_contact->error()) {
				app_log("Error sending registration confirmation: " . $_contact->error(), 'error', __FILE__, __LINE__);
				$page->addError("Sorry, we were unable to complete your registration");
				goto load;
			}

			// Redirect to Address Page If Order Started
			if (isset($target)) $next_page = $target;
			elseif (isset($order_id)) $next_page = "/_cart/address";
			else $next_page = "/_register/thank_you";
			header("Location: $next_page");
		}

		// Process Contact Entries
		app_log("Processing contact entries", 'debug', __FILE__, __LINE__);

		foreach (($_REQUEST['type'] ?? []) as $contact_id => $value) {
			if (!isset($_REQUEST['type'][$contact_id]) || empty($_REQUEST['type'][$contact_id])) continue;

			if ($contact_id > 0) {
				app_log("Updating contact record", 'debug', __FILE__, __LINE__);
				$contact = new \Register\Contact($contact_id);

				// Check if notify is set before accessing it
				$notify = isset($_REQUEST['notify'][$contact_id]) ? $_REQUEST['notify'][$contact_id] : false;

				// Check if public is set before accessing it
				$public = isset($_REQUEST['public'][$contact_id]) ? $_REQUEST['public'][$contact_id] : false;

				if (!$contact->validType($_REQUEST['type'][$contact_id])) {
					$page->addError("Invalid contact type: " . $_REQUEST['type'][$contact_id]);
				} elseif (!$contact->validValue($_REQUEST['type'][$contact_id], $_REQUEST['value'][$contact_id])) {
					$page->addError("Invalid value for contact type: " . $_REQUEST['type'][$contact_id]);
				} else {
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
					if (!$noChanges) $contact->auditRecord("USER_UPDATED", "Customer Contact updated: " . implode(", ", $contactRecord));

					$contact->update($contactRecord);
					if ($contact->error()) {
						$page->addError("Error updating contact: " . $customer->error());
						goto load;
					}
				}
			} else {

				app_log("Adding contact record", 'debug', __FILE__, __LINE__);
				if (($_REQUEST['notify'][0] ?? false)) $notify = true;
				else $notify = false;

				// Get the public flag for the new contact
				if (isset($_REQUEST['public'][0]) && $_REQUEST['public'][0] == '1') {
					$public = true;
				} else {
					$public = false;
				}

				// Create Contact Record
				$contactRecord = array(
					"person_id" => $customer->id,
									"type" => $_REQUEST['type'][0] ?? '',
				"description" => noXSS($_REQUEST['description'][0] ?? ''),
				"value" => $_REQUEST['value'][0] ?? '',
				"notes" => $_REQUEST['notes'][0] ?? '',
					"notify" => $notify,
					"public" => $public
				);
				$contact = $customer->addContact($contactRecord);
				if ($customer->error()) {
					$page->addError("Error adding contact: " . $customer->error());
					goto load;
				}
				$contact->auditRecord("USER_UPDATED", "Customer Contact added: " . implode(", ", $contactRecord));
			}
		}

		# Get List Of Possible Roles
		app_log("Checking roles", 'notice', __FILE__, __LINE__);
		$rolelist = new \Register\RoleList();
		$available_roles = $rolelist->find();
		app_log("Found " . $rolelist->count() . " roles", 'trace', __FILE__, __LINE__);
		$page->success = 'Your changes have been saved';
	}
}

// handle send another verification email
if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Resend Email" && !$readOnly) {

	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
		$page->addError("Invalid Request");
	} else {

		$validation_key = md5(microtime());
		$customer->update(array('validation_key' => $validation_key));

		// create the verify account email
		$verify_url = $GLOBALS['_config']->site->hostname . '/_register/new_customer?method=verify&access=' . $validation_key . '&login=' . $customer->code;
		if ($GLOBALS['_config']->site->https) $verify_url = "https://$verify_url";
		else $verify_url = "http://$verify_url";

		$template = new \Content\Template\Shell(
			array(
				'path'	=> $GLOBALS['_config']->register->verify_email->template,
				'parameters'	=> array(
					'VERIFYING.URL' => $verify_url,
					'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? ''
				)
			)
		);
		if ($template->error()) {
			app_log($template->error(), 'error');
			$page->addError("Error generating verification email, please contact us at " . $GLOBALS['_config']->site->support_email . " to complete your registration, thank you!");
		} else {
			$message = new \Email\Message($GLOBALS['_config']->register->verify_email);
			$message->html(true);
			$message->body($template->output());
			if (! $customer->notify($message)) {
				$page->addError("Confirmation email could not be sent, please contact us at " . $GLOBALS['_config']->site->support_email . " to complete your registration, thank you!");
				app_log("Error sending confirmation email: " . $customer->error(), 'error');
			} else {
				$page->success = "You have been issued another verification email.";
			}
		}
	}
}

load:
if ($customer->id) {
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

// get customer queued status
$queuedCustomer = new \Register\Queue();
$queuedCustomer->getByQueuedLogin($customer->id);

if (! isset($target)) $target = '';
