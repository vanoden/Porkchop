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
	}
	elseif ($GLOBALS['_SESSION_']->customer->id) {
		$customer = new \Register\Customer($GLOBALS['_SESSION_']->customer->id);
	}
	else {
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

	app_log($GLOBALS['_SESSION_']->customer->code." accessing account of customer ".$customer->id,'notice',__FILE__,__LINE__);

	#######################################
	### Handle Actions					###
	#######################################
	// handle form "delete" submit
	if (isset($_REQUEST['submit-type']) && $_REQUEST['submit-type'] == "delete-contact" && !$readOnly) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
		}
		else {
		    $contact = new \Register\Contact($_REQUEST['register-contacts-id']);
		    $contact->delete();
		    $page->success = 'Contact Entry ' . $_REQUEST['register-contacts-id'] . ' has been removed.';
			$contact->auditRecord('USER_UPDATED', 'Contact Entry ' . $contact->type . ' ' . $contact->value . ' ' . $contact->notes . ' ' . $contact->description . ' has been removed.');
		}
	}
	
	// handle form "apply" submit
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply" && !$readOnly) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
		}
		else {
			app_log("Account form submitted",'debug',__FILE__,__LINE__);
			$parameters = array();

			if (! validTimezone($_REQUEST['timezone'])) $_REQUEST['timezone'] = 'America/New_York';

			if (isset($_REQUEST["first_name"])) 	$parameters['first_name']	= noXSS($_REQUEST["first_name"]);
			if (isset($_REQUEST["last_name"]))		$parameters['last_name']	= noXSS($_REQUEST["last_name"]);
			if (isset($_REQUEST["timezone"]))		$parameters['timezone']		= $_REQUEST["timezone"];
			if (isset($_REQUEST["password"]) and ($_REQUEST["password"])) {
				if ($_REQUEST["password"] != $_REQUEST["password_2"]) {
					$page->addError("Passwords do not match");
					goto load;
				}
				else
					$parameters["password"] = $_REQUEST["password"];
			}

			if ($customer->id) {
				app_log("Updating customer ".$customer->id,'debug',__FILE__,__LINE__);

				$customer->update($parameters);
				
				// set the user to active if they're expired, this will ensure then can continue to login
				if (isset($parameters["password"])) {
					if ($customer->status == 'EXPIRED') $customer->update(array('status' => 'ACTIVE'));
				}
				
				if ($customer->error()) {
					app_log("Error updating customer: ".$customer->error(),'error',__FILE__,__LINE__);
					$page->addError("Error updating customer information.  Our admins have been notified.  Please try again later");
					goto load;
				}
				
			}
			else {
			
				### THIS NEVER HAPPENS ###
				app_log("New customer registration",'debug',__FILE__,__LINE__);
				
				# Default Login to Email Address
				if (! $_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['email_address'];

				# Generate Validation Key
				$validation_key = md5(microtime());

				$parameters["login"] = $_REQUEST['login'];

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
						$page->addError("Error updating session: ".$GLOBALS['_SESSION_']->error);
						goto load;
					}
				}

				// Registration Confirmation
				$_contact = new \Register\Contact();
				$_contact->notify(array(
						"from"		=> $GLOBALS['_config']->register->confirmation->from,
						"subject"	=> $GLOBALS['_config']->register->confirmation->subject,
						"message"	=> "Thank you for registering",
					)
				);
				if ($_contact->error()) {
					app_log("Error sending registration confirmation: ".$_contact->error(),'error',__FILE__,__LINE__);
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
			app_log("Processing contact entries",'debug',__FILE__,__LINE__);
			
			foreach ($_REQUEST['type'] as $contact_id => $value) {
				if (! isset($_REQUEST['type'][$contact_id]) || empty($_REQUEST['type'][$contact_id])) continue;

				if ($contact_id > 0) {

					app_log("Updating contact record",'debug',__FILE__,__LINE__);
					$contact = new \Register\Contact($contact_id);

					if ($_REQUEST['notify'][$contact_id]) $notify = true;
					else $notify = false;
					
					if (! $contact->validType($_REQUEST['type'][$contact_id])) {
    					$page->addError("Invalid contact type: " . $_REQUEST['type'][$contact_id]);
					} elseif (! $contact->validValue($_REQUEST['type'][$contact_id],$_REQUEST['value'][$contact_id])) {
    					$page->addError("Invalid value for contact type: " . $_REQUEST['type'][$contact_id]);
					} else {

						// Update Existing Contact Record
						$contactRecord = array(
							"type"			=> $_REQUEST['type'][$contact_id],
							"description"	=> noXSS(trim($_REQUEST['description'][$contact_id])),
							"value"			=> $_REQUEST['value'][$contact_id],
							"notes"			=> noXSS(trim($_REQUEST['notes'][$contact_id])),
							"notify"		=> $notify
						);
						

						$noChanges = (
							$contact->type == $contactRecord['type'] &&
							$contact->description == $contactRecord['description'] &&
							$contact->value == $contactRecord['value'] &&
							$contact->notes == $contactRecord['notes'] &&
							$contact->notify == $contactRecord['notify']
						);
						if (!$noChanges) $contact->auditRecord("USER_UPDATED","Customer Contact updated: " . implode(", ", $contactRecord));
						
						$contact->update($contactRecord);
						if ($contact->error()) {
							$page->addError("Error updating contact: ".$customer->error());
							goto load;
						}
					}
				}
				else {

					app_log("Adding contact record",'debug',__FILE__,__LINE__);
					if ($_REQUEST['notify'][0]) $notify = true;
					else $notify = false;

					// Create Contact Record
					$contactRecord = array(
						"person_id"		=> $customer->id,
						"type"			=> $_REQUEST['type'][0],
						"description"	=> noXSS($_REQUEST['description'][0]),
						"value"			=> $_REQUEST['value'][0],
						"notes"			=> $_REQUEST['notes'][0],
						"notify"		=> $notify
					);
					$contact = $customer->addContact($contactRecord);
					if ($customer->error()) {
						$page->addError("Error adding contact: ".$customer->error());
						goto load;
					}
					$contact->auditRecord("USER_UPDATED","Customer Contact added: " . implode(", ", $contactRecord));
				}
			}

			# Get List Of Possible Roles
			app_log("Checking roles",'notice',__FILE__,__LINE__);
			$rolelist = new \Register\RoleList();
			$available_roles = $rolelist->find();
			app_log("Found ".$rolelist->count()." roles",'trace',__FILE__,__LINE__);
			$page->success = 'Your changes have been saved';
		}
	}
	
	// handle send another verification email
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Resend Email" && !$readOnly) {
	
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
			$page->addError("Invalid Request");
		} else {

			$validation_key = md5(microtime());
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
	app_log("Loading Organizations",'trace',__FILE__,__LINE__);
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();
	$_contact = new \Register\Contact();
	$contact_types = $_contact->types;
	
	// get customer queued status
	$queuedCustomer = new \Register\Queue(); 
	$queuedCustomer->getByQueuedLogin($customer->id);

	if (! isset($target)) $target = '';