<?php
	###########################################################
	### login_mc.php										###
	### This program is the main content file for			###
	### login.php.  This program allows a customer to		###
	### identify themselves via login name and password.	###
	### It will flag the session and order record with		###
	### the customer id if login successful.				###
	### A. Caravello 8/25/2002								###
	###########################################################
	$page = new \Site\Page();
	$CAPTCHA_GO = false;

	# Handle Post-Login Redirects
    $target = "";
	if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'true') {
		# This Is How They SHOULD Come In from Redirect
		if (isset($_REQUEST['module']) && isset($_REQUEST['view'])) {
			$target = "/_".$_REQUEST['module']."/".$_REQUEST['view'];
		} else {
			$target = $GLOBALS['_REQUEST_']->refererURI();
			app_log("Return to ".$GLOBALS['_REQUEST_']->refererURI()." after login");
		}
	} elseif (isset($_POST['login_target']))
		# This is how the SHOULD come in from FORM submit
		$target = $_POST['login_target'];
	elseif(isset($_GET['target']))
		# Translate target
		$target = urldecode($_GET["target"]);
	elseif($GLOBALS['_config']->register->auth_target)
		$target = $GLOBALS['_config']->register->auth_target;
	if (! preg_match('/^\//',$target))
		$target = '/'.$target;

	if (($GLOBALS['_SESSION_']->customer->id) and ($target != '/'))	{
		app_log("Redirecting ".$GLOBALS['_SESSION_']->customer->code." to ".PATH.$target,'notice',__FILE__,__LINE__);
		header("location: ".PATH.$target);
		exit;
	}

	# Handle Input
	if (isset($_REQUEST['token']) and (preg_match('/^[a-f0-9]{64}$/',$_REQUEST['token']))) {
		app_log('Auth By Token','debug',__FILE__,__LINE__);
		# Consume Token
		$token = new \Register\PasswordToken();
		$customer_id = $token->consume($_REQUEST['token']);
		if ($token->error) {
			app_log("Error in password recovery: ".$token->error,'error',__FILE__,__LINE__);
			$page->addError("Error in password recovery.  Admins have been notified.  Please try again later.");
		} elseif ($customer_id > 0) {
			$customer = new \Register\Customer($customer_id);
			if ($customer->error) {
				app_log("Error getting customer: ".$customer->error,'error',__FILE__,__LINE__);
				$page->addError("Token error");
			} elseif(! $customer->id) {
				app_log("Customer not found!",'notice',__FILE__,__LINE__);
				$page->addError("Token error");
			} else {
				$GLOBALS['_SESSION_']->assign($customer->id);

				app_log("Customer ".$customer->id." logged in by token",'notice',__FILE__,__LINE__);
				app_log("Redirecting to '/_register/reset_password'",'notice',__FILE__,__LINE__);
				header("location: /_register/reset_password");
				exit;
			}
		} else {
			$page->addError("Sorry, your recovery token was not recognized or has expired");
		}
	}
	elseif (isset($_REQUEST['login'])) {
		app_log("Auth by login/password",'debug',__FILE__,__LINE__);
		$customer = new \Register\Customer();
		if ($customer->get($_REQUEST['login'])) {
			if ($customer->isBlocked()) {
				$page->addError("Your account has been blocked");
				$failure = new \Register\AuthFailure();
				$failure->add($_SERVER['REMOTE_ADDR'],$_REQUEST['login'],'INACTIVE',$_SERVER['PHP_SELF']);
				return;
			}
			if ($customer->status == 'EXPIRED' || $customer->auth_failures() >= 3) {
				$CAPTCHA_GO = true;
				if (!isset($_REQUEST['g-recaptcha-response'])) {
					// CAPTCHA Required but not done
					app_log("Customer ".$customer->id. " " . $customer->status . " login ATTEMPTED",'notice',__FILE__,__LINE__);
					app_log("login_target = $target",'debug',__FILE__,__LINE__);
					$page->addError("CAPTCHA Required");
					return;
				}
				else {
					// CAPTCHA Required and Provided
					$reCAPTCHA = new \GoogleAPI\ReCAPTCHA();
					if ($reCAPTCHA->test($customer,$_REQUEST['g-recaptcha-response'])) {
						// CAPTCHA Confirmed, Go ahead to sign in
					}
					else {
						$page->addError("CAPTCHA Failed: ".$reCAPTCHA->error());
					}
				}
			}
			if (! $customer->authenticate($_REQUEST['login'],$_REQUEST['password'])) {
    			app_log("Customer ".$customer->id." login failed",'notice',__FILE__,__LINE__);
				app_log("login_target = $target",'debug',__FILE__,__LINE__);
				$counter = new \Site\Counter("auth_failed");
				$counter->increment();
				$page->addError("Authentication Failed");
				if ($customer->status == 'EXPIRED' || $customer->auth_failures() >= 3) $CAPTCHA_GO = true;
			}
			elseif ($customer->error) {
				app_log("Error in authentication: ".$customer->error,'error',__FILE__,__LINE__);
				$page->addError("Application Error");
			}
			elseif ($customer->message) {
				$page->addError($customer->message);
			}
			elseif (!$customer->isActive()) {
				$page->addError("This account is ".$customer->status);
			}
			else {
				$GLOBALS['_SESSION_']->assign($customer->id);
				$GLOBALS['_SESSION_']->touch();
				$customer->update(array("status" => "ACTIVE", "auth_failures" => 0));

				app_log("Customer ".$customer->id." logged in",'debug',__FILE__,__LINE__);
				app_log("login_target = $target",'debug',__FILE__,__LINE__);
				app_log("Redirecting to ".PATH.$target,'debug',__FILE__,__LINE__);
				header("location: ".PATH.$target);
				exit;
			}
		}
		else {
			// Customer doesn't exist
		}
	}
	else {
		app_log("No authentication information sent",'debug',__FILE__,__LINE__);
	}
