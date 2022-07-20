<?php

	$page = new \Site\Page();

	if (is_string($GLOBALS['_SESSION_']->status) && $GLOBALS['_SESSION_']->status == 'BLOCKED') {
		app_log('Account blocked','warn');
		$page->addError("Your account has been blocked.  Please contact ".$GLOBALS['_config']->site->support_email." for assistance.");
		$page->addError($GLOBALS['_SESSION_']->status);
	}
	// See if we received a parseable token
	elseif (isset($_REQUEST['token']) and (preg_match('/^[a-f0-9]{64}$/',$_REQUEST['token']))) {
		app_log('Auth By Token','debug',__FILE__,__LINE__);

		// Consume Token
		$token = new \Register\PasswordToken();
		$customer_id = $token->consume($_REQUEST['token']);
		if ($token->error) {
			app_log("Error in password recovery: ".$token->error,'error',__FILE__,__LINE__);
			$page->addError("Error in password recovery.  Admins have been notified.  Please try again later.");
		}
		elseif ($customer_id > 0) {
			// Grab Customer Instance
			$customer = new \Register\Customer($customer_id);
			if ($customer->error) {
				app_log("Error getting customer: ".$customer->error,'error',__FILE__,__LINE__);
				$page->addError("Token error");
			}
			elseif(! $customer->id) {
				app_log("Customer not found!",'notice',__FILE__,__LINE__);
				$page->addError("Token error");
			}
			else {
			
                // assign a super elevated user session for password reset
				$GLOBALS['_SESSION_']->assign($customer->id, true);
				app_log("Customer ".$customer->id." logged in by token",'notice',__FILE__,__LINE__);
			}
		}
		else {
			$page->addError("Sorry, your recovery token was not recognized or has expired");
		}
	}
	elseif (isset($_REQUEST["password"])) {
		if (! $GLOBALS['_SESSION_']->superElevated()) {
			// Check current password
			$checkUser = new \Register\Customer();
			if (! $checkUser->authenticate($GLOBALS['_SESSION_']->customer->login,$_REQUEST['current_password'])) {
				app_log("SuperElevation failed: user ".$GLOBALS['_SESSION_']->customer->login." pass ".$_REQUEST['current_password'],"warn");
				$page->addError("Current password check failed");
				return;
			}
		}
		app_log("Reset Password form submitted",'debug',__FILE__,__LINE__);
		$customerUpdated = false;

		// Get Customer Record
		$customer_id = $GLOBALS['_SESSION_']->customer->id;
		$customer = new \Register\Customer($customer_id);

		// check for errors
		if ($_REQUEST["password"] != $_REQUEST["password_2"]) $page->error .= "Passwords do not match";

		// Check Password Complexity
		if ($customer->password_strength($_REQUEST["password"]) < $GLOBALS['_config']->register->minimum_password_strength) $page->error .= "Password needs more complexity.".$customer->password_strength($_REQUEST["password"]);

		// If no errors and customer found, go ahead and update
		if (empty($page->error) && $customer->id) {
			app_log("Updating customer ".$customer_id,'debug',__FILE__,__LINE__);
			if ($customer->changePassword($_REQUEST["password"])) {
	        	// set the user to active if they're expired, this will ensure then can continue to login
				if (in_array($customer->status,array('EXPIRED','BLOCKED'))) $customer->update(array('status' => 'ACTIVE'));

				if ($customer->error()) {
					app_log("Error updating customer: ".$customer->error,'error',__FILE__,__LINE__);
					$page->addError("Error updating customer password.  Our admins have been notified.  Please try again later");
				}
				else {
					$page->success = 'Your password has been updated. Log in to continue';
					$GLOBALS['_SESSION_']->expire();
				}
			}
			else $page->addError($customer->error());
		}
	}