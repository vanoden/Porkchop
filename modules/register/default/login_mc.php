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

	if (empty($_REQUEST['csrfToken'])) $_REQUEST['csrfToken'] = null;
    // Check Risk Level from Host
	$CAPTCHA_GO = false;
	$remote_host = new \Network\Host();
	if ($remote_host->getByIPAddress($_SERVER['REMOTE_ADDR'])) {
		if ($remote_host->CAPTCHARequired()) {
			$CAPTCHA_GO = true;
		}
	}

	// Choose Target URL
	$target = "";
	if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'true') {
		# This Is How They SHOULD Come In from Redirect
		if (isset($_REQUEST['module']) && isset($_REQUEST['view'])) {
			if (!$page->validModule()) {
				$page->addError("Invalid target requested");
			}
			elseif(!$page->validView($_REQUEST['view'])) {
				$page->addError("Invalid target requested");
			}
			else {
				$target = "/_".$_REQUEST['module']."/".$_REQUEST['view'];
			}
		}
		else {
			$target = $GLOBALS['_REQUEST_']->refererURI();
			app_log("Return to ".$GLOBALS['_REQUEST_']->refererURI()." after login");
		}
	}
	elseif (isset($_POST['login_target'])) {
		# This is how the SHOULD come in from FORM submit
		$target = $_POST['login_target'];
		if (!preg_match('/^[\/\w\-\.\_]+$/',$target)) $target = '';
		app_log("login_target = ".$GLOBALS['_config']->register->auth_target);
	}
	elseif(isset($_GET['target'])) {
		# Translate target
		$target = urldecode($_GET['target']);
		# Validate target
		if (!preg_match('/^[\/\w\-\.\_]+$/',$target)) $target = '';
		app_log("target = ".$GLOBALS['_config']->register->auth_target);
	}
	elseif($GLOBALS['_config']->register->auth_target) {
		$target = $GLOBALS['_config']->register->auth_target;
		app_log("auth_target = ".$GLOBALS['_config']->register->auth_target);
	}

	if (! preg_match('/^\//',$target))
		$target = '/'.$target;

	if (($GLOBALS['_SESSION_']->customer->id) and ($target != '/'))	{
		app_log("Redirecting ".$GLOBALS['_SESSION_']->customer->code." to ".PATH.$target,'notice',__FILE__,__LINE__);
		header("location: ".PATH.$target);
		exit;
	}

	// Attempt to Authenticate with Temporary Token
	$token = new \Register\PasswordToken();
	if (isset($_REQUEST['token']) && $token->validCode($_REQUEST['token'])) {
		app_log('Auth By Token','debug',__FILE__,__LINE__);
		# Consume Token
		$customer_id = $token->consume($_REQUEST['token']);
		if ($token->error()) {
			app_log("Error in password recovery: ".$token->error(),'error',__FILE__,__LINE__);
			$page->addError("Error in password recovery.  Admins have been notified.  Please try again later.");
		}
		elseif ($customer_id > 0) {
			$customer = new \Register\Customer($customer_id);
			if ($customer->error()) {
				app_log("Error getting customer: ".$customer->error(),'error',__FILE__,__LINE__);
				$page->addError("Token error");
			}
			elseif(! $customer->id) {
				app_log("Customer not found!",'notice',__FILE__,__LINE__);
				$page->addError("Token error");
			}
			else {
				$GLOBALS['_SESSION_']->assign($customer->id);

				app_log("Customer ".$customer->id." logged in by token",'notice',__FILE__,__LINE__);
				app_log("Redirecting to '/_register/reset_password'",'notice',__FILE__,__LINE__);
				header("location: /_register/reset_password");
				exit;
			}
		}
		else {
			$page->addError("Sorry, your recovery token was not recognized or has expired");
		}
	}
	elseif (!empty($_REQUEST['login'])) {
		app_log("Auth by login/password",'debug',__FILE__,__LINE__);
		$customer = new \Register\Customer();
		if ($customer->validLogin($_REQUEST['login'])) {
			if ($customer->get($_REQUEST['login'])) {
				if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
					$page->addError("Invalid Request");
					$failure = new \Register\AuthFailure();
					$failure->add($_SERVER['REMOTE_ADDR'],$_REQUEST['login'],'CSRFTOKEN',$_SERVER['PHP_SELF']);
				}
				else {
					if ($customer->isBlocked()) {
						$page->addError("Your account has been blocked");
						$counter = new \Site\Counter("auth_failed");
						$counter->increment();
						$failure = new \Register\AuthFailure();
						$failure->add($_SERVER['REMOTE_ADDR'],$_REQUEST['login'],'INACTIVE',$_SERVER['PHP_SELF']);
						return;
					}
					elseif (!empty($GLOBALS['_config']->captcha->bypass_key) && !empty($_REQUEST['captcha_bypass_key']) && $GLOBALS['_config']->captcha->bypass_key == $_REQUEST['captcha_bypass_key']) {
						//Don't require catcha
					}
					elseif ($customer->status == 'EXPIRED' || $customer->auth_failures() >= 3) {
						$CAPTCHA_GO = true;
						if (!isset($_REQUEST['g-recaptcha-response'])) {
							// CAPTCHA Required but not done
							$counter = new \Site\Counter("captcha_block");
							$counter->increment();
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
					elseif ($customer->error()) {
						app_log("Error in authentication: ".$customer->error(),'error',__FILE__,__LINE__);
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
			}
			else {
				$page->addError("Login failed.");
			}
		}
		else {
			$page->addError("Invalid Login");
		}
	}
	else {
		app_log("No authentication information sent",'debug',__FILE__,__LINE__);
	}
