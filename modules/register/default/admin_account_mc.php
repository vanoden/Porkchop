<?php
	###################################################
	## register_mc.php								###
	## This program collects registration info		###
	## for the user.								###
	## A. Caravello 11/12/2002						###
	###################################################

	$page = new \Site\Page(array("module" => 'register',"view" => 'account'));
	$page->requirePrivilege('manage customers');

	$customer = new \Register\Customer();
	
	if (isset($_REQUEST['customer_id']) && preg_match('/^\d+$/',$_REQUEST['customer_id'])) $customer_id = $_REQUEST['customer_id'];
	elseif (preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$customer->get($code);
		if ($customer->id)
			$customer_id = $customer->id;
		else
			$page->addError("Customer not found");
	} else $customer_id = $GLOBALS['_SESSION_']->customer->id;

	app_log($GLOBALS['_SESSION_']->customer->login." accessing account of customer ".$customer_id,'notice',__FILE__,__LINE__);

	#######################################
	## Handle Actions					###
	#######################################

	// handle form "delete" submit
	if (isset($_REQUEST['submit-type']) && $_REQUEST['submit-type'] == "delete-contact") {
	    // Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid request");
			return 403;
		}
		else {
		    $_contact = new \Register\Contact($_REQUEST['register-contacts-id']);
		    $_contact->delete();
		    $page->success = 'Contact Entry ' . $_REQUEST['register-contacts-id'] . ' has been removed.';
		}
	}

	// handle form "apply" submit
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply") {	    // Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid request");
			return 403;
		}
		else {
			app_log("Account form submitted",'debug',__FILE__,__LINE__);
			$parameters = array();
			if (! $customer->validLogin($_REQUEST['login']))
				$page->addError("Invalid login");
			elseif (! $customer->validStatus($_REQUEST['status']))
				$page->addError("Invalid status ".$_REQUEST['status']);
			else {
				$parameters['login'] = $_REQUEST["login"];
				if (isset($_REQUEST["first_name"]) && preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST["first_name"]))
					$parameters['first_name']	= $_REQUEST["first_name"];
				if (isset($_REQUEST["last_name"]) && preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST["last_name"]))
					$parameters['last_name']	= $_REQUEST["last_name"];
				if (isset($_REQUEST["timezone"]))		$parameters['timezone']		= $_REQUEST["timezone"];
				if (isset($_REQUEST["status"]))			$parameters['status']		= $_REQUEST["status"];
				if (isset($_REQUEST["automation"])) {
					if ($_REQUEST['automation']) $parameters['automation'] = true;
					else $parameters['automation'] = false;
				}
				if (isset($_REQUEST['organization_id'])) $parameters["organization_id"] = $_REQUEST["organization_id"];
				if (isset($_REQUEST["password"]) and ($_REQUEST["password_2"])) {
					if ($_REQUEST["password"] != $_REQUEST["password_2"]) {
						$page->addError("Passwords do not match");
						goto load;
					}
				}

				if ($customer_id) {
					app_log("Updating customer ".$customer_id,'debug',__FILE__,__LINE__);
					$customer = new \Register\Customer($customer_id);
					$customer->update($parameters);
					if ($customer->error) {
						app_log("Error updating customer: ".$customer->error,'error',__FILE__,__LINE__);
						$page->addError("Error updating customer information.  Our admins have been notified.  Please try again later");
						goto load;
					}
					if ($_REQUEST['password']) {
						if (!$customer->changePassword($_REQUEST["password"])) {
						$page->addError("Password needs more complexity");
						}
					}
				}
				else {
					app_log("New customer registration",'debug',__FILE__,__LINE__);

					// Default Login to Email Address
					if (! $_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['email_address'];

					// Generate Validation Key
					$validation_key = md5(microtime());

					$parameters["login"] = $_REQUEST['login'];

					###########################################
					## Add User To Database
					###########################################
					
					// Add Customer Record to Database
					$customer = new \Register\Customer();
					$customer->add($parameters);

					if ($customer->error) {
						$page->addError($customer->error);
						goto load;
					}

					if ($customer->id) {
						$GLOBALS['_SESSION_']->update(array("user_id" => $customer->id));
						if ($GLOBALS['_SESSION_']->error) {
							$page->addError("Error updating session: ".$GLOBALS['_SESSION_']->error);
							goto load;
						}
					}

					if (empty($_REQUEST['password'])) {
						$_REQUEST['password'] = $customer->randomPassword();
					}
					$customer->changePassword($_REQUEST['password']);

					$template = new \Content\Template\Shell($GLOBALS['_config']->register->account_created->template);
					$template->addParam('URL',$GLOBALS['_config']->site->url);
					$template->addParam('WEBSITE',$GLOBALS['_config']->site->hostname);
					$template->addParam('LOGIN',$_REQUEST['login']);
					$template->addParam('PASSWORD',$_REQUEST['password']);

					$message = new \Email\Message();
					$message->from($GLOBALS['_config']->register->confirmation->from);
					$message->subject($GLOBALS['_config']->register->confirmation->subject);
					$message->body($template->output());
			
					// Registration Confirmation
					$customer->notify($message);
					if ($customer->error) {
						app_log("Error sending registration confirmation: ".$_contact->error,'error',__FILE__,__LINE__);
						$page->addError("Sorry, we were unable to complete your registration");
						goto load;
					}

					// Redirect to Address Page If Order Started
					if (isset($target)) $next_page = $target;
					elseif (isset($order_id)) $next_page = "/_cart/address";
					else $next_page = "/_register/thank_you";
					header("Location: $next_page");
				}
			}
			
			// Process Contact Entries
			app_log("Processing contact entries",'debug',__FILE__,__LINE__);
			foreach ($_REQUEST['type'] as $contact_id => $type) {
				if (! $_REQUEST['type'][$contact_id]) continue;

				if ($contact_id > 0) {
					app_log("Updating contact record",'debug',__FILE__,__LINE__);
					$contact = new \Register\Contact($contact_id);
                    if ($contact->error()) {
                        $page->addError($contact->error());
                    }
                    else {
                        if ($_REQUEST['notify'][$contact_id]) $notify = true;
                        else $notify = false;

                        if (! $contact->validType($_REQUEST['type'][$contact_id]))
                            $page->addError("Invalid contact type");
                        elseif (! $contact->validValue($_REQUEST['type'][$contact_id],$_REQUEST['value'][$contact->id]))
                            $page->addError("Invalid value for contact type ".$_REQUEST['type'][$contact_id]);
                        else {
                            // Update Existing Contact Record
                            $contact->update(
                                array(
                                    "type"			=> $_REQUEST['type'][$contact_id],
                                    "description"	=> noXSS(trim($_REQUEST['description'][$contact_id])),
                                    "value"			=> $_REQUEST['value'][$contact_id],
                                    "notes"			=> noXSS(trim($_REQUEST['notes'][$contact_id])),
                                    "notify"		=> $notify
                                )
                            );
                            if ($contact->error()) {
                                $page->addError("Error updating contact: ".$customer->error());
                                goto load;
                            }
                        }
                    }
				}
				else {
					app_log("Adding contact record",'debug',__FILE__,__LINE__);
					if ($_REQUEST['notify'][0]) $notify = true;
					else $notify = false;

					$contact = new \Register\Contact($contact_id);
                    if ($contact->error()) {
                        $page->addError($contact->error());
                    }
                    else {
                        // Create Contact Record
                        if (! $contact->validType($_REQUEST['type'][0]))
                            $page->addError("Invalid contact type");
                        elseif (! $contact->validValue($_REQUEST['type'][0],$_REQUEST['value'][0]))
                            $page->addError("Invalid value for contact type");
                        else {
                            $customer->addContact(
                                array(
                                    "person_id"		=> $customer_id,
                                    "type"			=> $_REQUEST['type'][0],
                                    "description"	=> noXSS(trim($_REQUEST['description'][0])),
                                    "value"			=> $_REQUEST['value'][0],
                                    "notes"			=> noXSS(trim($_REQUEST['notes'][0])),
                                    "notify"		=> $notify
                                )
                            );

                            if ($customer->error()) {
                                $page->addError("Error adding contact: ".$customer->error());
                                goto load;
                            }
                        }
                    }
				}
			}

			// Get List Of Possible Roles
			app_log("Checking roles",'notice',__FILE__,__LINE__);
			$rolelist = new \Register\RoleList();
			$available_roles = $rolelist->find();
			app_log("Found ".$rolelist->count()." roles",'trace',__FILE__,__LINE__);

			// Loop through all roles and apply changes if necessary
			foreach ($available_roles as $role) {
				app_log("Checking role ".$role->name."[".$role->id."]",'trace',__FILE__,__LINE__);
				if (isset($_REQUEST['role'][$role->id]) && $_REQUEST['role'][$role->id]) {
					app_log("Role is selected",'trace',__FILE__,__LINE__);
					if (! $customer->has_role($role->name)) {
						app_log("Adding role ".$role->name." for ".$customer->login,'debug',__FILE__,__LINE__);
						$customer->add_role($role->id);
					}
				}
				else {
					app_log("Role is not selected",'trace',__FILE__,__LINE__);
					if ($customer->has_role($role->name)){
						app_log("Role ".$role->name." being revoked from ".$customer->login,'debug',__FILE__,__LINE__);
						$customer->drop_role($role->id);
					}
				}
			}
			$page->success = 'Your changes have been saved';
		}
	}

	if (isset($_REQUEST["btnResetFailures"])) {
		// Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid request");
			return 403;
		}
		else {
			$customer = new \Register\Customer($_REQUEST['customer_id']);
			$customer->resetAuthFailures();
		}
	}

	load:
	if ($customer_id) {
		$customer = new \Register\Customer($customer_id);
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

	if (!empty($customer->login)) {
		$authFailureList = new \Register\AuthFailureList();
		$authFailures = $authFailureList->find(array('_limit' => 5,'login' => $customer->login));
	} else {
		$authFailures = array();
	}

	if (! isset($target)) $target = '';	
