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

	// Set CAPTCHA public key for template use
	$captcha_public_key = '';
	if (!empty($GLOBALS['_config']->captcha->public_key)) {
		$captcha_public_key = $GLOBALS['_config']->captcha->public_key;
	}
	else {
		app_log("CAPTCHA Not Configured",'warn');
	}

	// reCAPTCHA configurable on/off for login
	$rc = $GLOBALS['_config']->register->requireCAPTCHA ?? null;
	$require_captcha_login = ($rc && isset($rc->login)) ? (bool) $rc->login : true;

	// Cannot demand CAPTCHA when no site key is configured (would be an unrecoverable dead end)
	$captcha_configured = ($require_captcha_login && !empty($captcha_public_key));
	if ($require_captcha_login && !$captcha_configured) {
		app_log("reCAPTCHA required for login but captcha->public_key is empty; skipping CAPTCHA gate", 'error', __FILE__, __LINE__);
	}

	$CAPTCHA_GO = false;
	$max_auth_failures_before_block = 6;
	$captcha_after_failures = 3;

	/**
	 * Build the blocked-account message shown on the login form.
	 * @param \Register\Customer $customer
	 * @param string|null $ticketCode Ticket reference created on this request, if any
	 * @return string
	 */
	$blockedAccountMessage = function(\Register\Customer $customer, $ticketCode = null): string {
		$supportEmail = $GLOBALS['_config']->site->support_email ?? 'service@spectrosinstruments.com';
		$message = "Your account has been locked after too many failed sign-in attempts. Further sign-in attempts will not succeed.";
		if (!empty($ticketCode)) {
			$message .= " A support ticket (".$ticketCode.") has been created and our staff have been notified.";
		}
		else {
			$message .= " Our support staff have been notified.";
		}
		$message .= " You can restore access using Recover Password.";
		$message .= " If you need help, contact <a href=\"mailto:".htmlspecialchars($supportEmail)."\">".htmlspecialchars($supportEmail)."</a>.";
		return $message;
	};

	// Choose Target URL
	$target = "";
	if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'true') {
		# This Is How They SHOULD Come In from Redirect
		if (isset($_REQUEST['module']) && isset($_REQUEST['view'])) {
			if (!$page->validModule($_REQUEST['module'])) {
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
		if (!preg_match('/[-\.\/\?\=\&_a-zA-Z0-9]+$/',$target)) $target = '';
		if (!isset($GLOBALS['_config']->register->auth_target)) app_log("auth_target not configured",'warning');
		else app_log("login_target = ".$target,'debug',__FILE__,__LINE__);
	}
	elseif(isset($_REQUEST['target'])) {

		# Translate target
		$target = urldecode($_REQUEST['target']);
		
		# Validate URL characters (include underscore for module/view names and path segments like RMA codes)
		if (!preg_match('/[-\.\/\?\=\&_a-zA-Z0-9]+$/',$target)) $target = '';
		app_log("target = ".$target,'debug',__FILE__,__LINE__);
	}
	elseif($GLOBALS['_config']->register->auth_target) {
		$target = $GLOBALS['_config']->register->auth_target;
		app_log("auth_target = ".$GLOBALS['_config']->register->auth_target);
	}

	if (! preg_match('/^\//',$target)) $target = '/'.$target;

	// Reject targets that point to login - prevents redirect loop when target=/_register/login?target=...
	$target_stripped = preg_replace('/\?.*$/', '', $target);
	if (preg_match('#^/_register/login/?$#', $target_stripped)) {
		$target = '';
		app_log("Rejected login-as-target to break redirect loop", 'notice');
	}

	if (($GLOBALS['_SESSION_']->customer_id) and ($target != '/'))	{
		app_log("Redirecting ".$GLOBALS['_SESSION_']->customer->code." to ".PATH.$target,'notice',__FILE__,__LINE__);
		header("Location: ".PATH.$target);
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
			else {if (!preg_match('/^[\/\w\-\.\_]+$/',$target)) $target = '';
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
				if ( !$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
					$page->addError("Invalid Request");
					$failure = new \Register\AuthFailure();
					$endpoint = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
					$failure->add(array($GLOBALS['_REQUEST_']->client_ip ?? '',$_REQUEST['login'],'CSRFTOKEN',$endpoint));
				}
				else {
					if ($customer->isBlocked()) {
						$page->addError($blockedAccountMessage($customer, null));
						$counter = new \Site\Counter("auth_failed");
						$counter->increment();
						$failure = new \Register\AuthFailure();
						$endpoint = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
						$failure->add(array($GLOBALS['_REQUEST_']->client_ip ?? '',$_REQUEST['login'],'INACTIVE',$endpoint));
						app_log("EXIT 1",'notice');
						return;
					}
					elseif (!empty($GLOBALS['_config']->captcha->bypass_key) && !empty($_REQUEST['captcha_bypass_key']) && $GLOBALS['_config']->captcha->bypass_key == $_REQUEST['captcha_bypass_key']) {
						//Don't require captcha
					}
					elseif ($captcha_configured && ($customer->status == 'EXPIRED' || $customer->auth_failures() >= $captcha_after_failures)) {
						$CAPTCHA_GO = true;
						if (empty($_REQUEST['g-recaptcha-response'])) {
							// CAPTCHA Required but not completed
							$counter = new \Site\Counter("captcha_block");
							$counter->increment();
							app_log("Customer ".$customer->id. " " . $customer->status . " login ATTEMPTED",'notice',__FILE__,__LINE__);
							app_log("login_target = $target",'debug',__FILE__,__LINE__);
							$page->addError("Please complete the CAPTCHA below to continue");
							app_log("EXIT 2",'notice');
							return;
						}
						else {
							// CAPTCHA Required and Provided
							$reCAPTCHA = new \GoogleAPI\ReCAPTCHA();
							if (!$reCAPTCHA->test($customer,$_REQUEST['g-recaptcha-response'])) {
								$page->addError("CAPTCHA Failed: ".$reCAPTCHA->error());
								$CAPTCHA_GO = true;
								return;
							}
						}
					}

					if (! $customer->authenticate($_REQUEST['login'],$_REQUEST['password'])) {
						app_log("Customer ".$customer->id." login failed",'notice',__FILE__,__LINE__);
						app_log("login_target = $target",'debug',__FILE__,__LINE__);
						$counter = new \Site\Counter("auth_failed");
						$counter->increment();

						// authenticate() may have just blocked the account and set last_blocked_ticket_code
						$ticketCode = !empty($customer->last_blocked_ticket_code) ? $customer->last_blocked_ticket_code : null;
						if ($customer->isBlocked()) {
							$page->addError($blockedAccountMessage($customer, $ticketCode));
						}
						else {
							$failures = (int) $customer->auth_failures();
							$remaining = $max_auth_failures_before_block - $failures;
							$page->addError("Authentication Failed");
							if ($remaining > 0) {
								$page->addError($remaining." attempt".($remaining === 1 ? '' : 's')." remaining before your account is locked");
							}
							if ($captcha_configured && ($customer->status == 'EXPIRED' || $failures >= $captcha_after_failures)) {
								$CAPTCHA_GO = true;
							}
						}
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
						$failure = new \Register\AuthFailure();
						$endpoint = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : (isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : '');
						$failure->add(array($GLOBALS['_REQUEST_']->client_ip ?? '',$_REQUEST['login'],'INACTIVE',$endpoint));
					} else {

						// populate the final target after the user logs in
						if (empty($target) || !isset($target)) $target = "/_register/account";
						
						// Check if user requires OTP
						app_log("DEBUG: About to call requiresOTP() for customer ID: ".$customer->id, 'debug', __FILE__, __LINE__);
						$otpRequired = $customer->requiresOTP();
						app_log("DEBUG: requiresOTP() returned ".($otpRequired ? 'true' : 'false')." for customer ID: ".$customer->id, 'debug', __FILE__, __LINE__);
						
						if ($otpRequired) {
							// Save the final target in the session for after OTP verification
							$OTPRedirect = $target;
							$target = "/_register/otp";

							// Assign the customer to the session and store the redirect target
							$GLOBALS['_SESSION_']->assign($customer->id, false, $OTPRedirect);
							$GLOBALS['_SESSION_']->touch();
							$GLOBALS['_SESSION_']->setOTPVerified(false);

							// Update customer status and reset auth failures in statistics
							$customer->update(array("status" => "ACTIVE"));
							$customer->statistics()->resetFailedLogins();

							// Redirect to OTP verification page
							header("Location: $target");
							exit;
						}

						# Update the Session
						$GLOBALS['_SESSION_']->assign($customer->id);
						
						# Mark OTP as verified since TOTP is not required
						$GLOBALS['_SESSION_']->setOTPVerified(true);

						app_log("Customer ".$customer->id." logged in",'debug',__FILE__,__LINE__);
						app_log("login_target = $target",'debug',__FILE__,__LINE__);
						app_log("Redirecting to ".PATH.$target,'debug',__FILE__,__LINE__);
						header("Location: ".PATH.$target);
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
