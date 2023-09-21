<?php
	$page = new \Site\Page();

	// This page requires either an emailed token or super-elevation (prev password)
	// So no extra Anti-CSRF measures required
    // Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
	//if (!empty($_POST) && ! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	//	$page->addError("Invalid request");
		#return 403;
	//}

	// See if we received a parseable token
	$token = new \Register\PasswordToken();
	if (isset($_REQUEST['token']) && $token->validCode($_REQUEST['token'])) {
		app_log('Auth By Token','debug',__FILE__,__LINE__);

		// Consume Token
		$customer_id = $token->consume($_REQUEST['token']);
		if ($token->error) {
			app_log("Error in password recovery: ".$token->error,'error',__FILE__,__LINE__);
			$page->addError("Error in password recovery.  Admins have been notified.  Please try again later.");
		}
		elseif ($customer_id > 0) {
			$GLOBALS['_SESSION_']->superElevate();
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
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
			return;
		}
		elseif (! $GLOBALS['_SESSION_']->superElevated()) {
			// Check current password
			$checkUser = new \Register\Customer();
			if (! $checkUser->authenticate($GLOBALS['_SESSION_']->customer->login,$_REQUEST['currentPassword'])) {
				app_log("SuperElevation failed: user ".$GLOBALS['_SESSION_']->customer->login." pass ".$_REQUEST['current_password'],"warn");
				$page->addError("Current password check failed");
				return;
			}
		}
		else {
			app_log("Reset Password form submitted",'debug',__FILE__,__LINE__);
			$customerUpdated = false;

			// Get Customer Record
			$customer_id = $GLOBALS['_SESSION_']->customer->id;
			$customer = new \Register\Customer($customer_id);

			// check for errors
			if ($_REQUEST["password"] != $_REQUEST["password_2"]) $page->addError("Passwords do not match");

			// Check Password Complexity
			if ($customer->password_strength($_REQUEST["password"]) < $GLOBALS['_config']->register->minimum_password_strength) $page->addError("Password needs more complexity.");

			// If no errors and customer found, go ahead and update
			if ($page->errorCount() < 1 && $customer->id) {
				app_log("Updating customer ".$customer_id,'debug',__FILE__,__LINE__);
				if ($customer->changePassword($_REQUEST["password"])) {
					// set the user to active if they're expired, this will ensure then can continue to login
					if (in_array($customer->status,array('EXPIRED','BLOCKED'))) $customer->update(array('status' => 'ACTIVE'));

					if ($customer->error()) {
						app_log("Error updating customer: ".$customer->error,'error',__FILE__,__LINE__);
						$page->addError("Error updating customer password.  Our admins have been notified.  Please try again later");
					}
					else {
						app_log("Expiring session and showing logged out page","info");
						if ($GLOBALS['_SESSION_']->expire()) {
							header("Location: /_register/reset_password?status=complete");
							exit;
						}
						else {
							$page->addError($GLOBALS['_SESSION_']->error());
						}
					}
				}
				else $page->addError($customer->error());
			}
		}
	}
