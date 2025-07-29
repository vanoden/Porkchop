<?php
	$site = new \Site();
	$page = $site->page();

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
		if ($token->error()) {
			app_log("Error in password recovery: ".$token->error(),'error',__FILE__,__LINE__);
			$page->addError("Error in password recovery.  Admins have been notified.  Please try again later.");
		} elseif ($customer_id > 0) {
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
			} else {
                // assign a super elevated user session for password reset
				$GLOBALS['_SESSION_']->assign($customer->id, true);
				app_log("Customer ".$customer->id." logged in by token",'notice',__FILE__,__LINE__);
			}
		} else {
			$page->addError("Sorry, your recovery token was not recognized or has expired");
			app_log("Customer not found for token ".$_REQUEST['token'],'info');
		}
	}
	elseif (isset($_REQUEST["password"])) {
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
			app_log("csrfToken missing or invalid",'info');
			return;
		} elseif (! $GLOBALS['_SESSION_']->superElevated()) {
			// Check current password
			$checkUser = new \Register\Customer();
			if (! $checkUser->authenticate($GLOBALS['_SESSION_']->customer->code,$_REQUEST['currentPassword'])) {
				app_log("SuperElevation failed: user ".$GLOBALS['_SESSION_']->customer->code." pass ".$_REQUEST['current_password'],"warn");
				$page->addError("Current password check failed");
				return;
			}
		}
		if ($page->errorCount() < 1) {
			app_log("Reset Password form submitted",'debug',__FILE__,__LINE__);
			$customerUpdated = false;

			// Get Customer Record
			$customer_id = $GLOBALS['_SESSION_']->customer->id;
			$customer = new \Register\Customer($customer_id);

			// check for errors
			if ($_REQUEST["password"] != $_REQUEST["password_2"]) {
				$page->addError("Passwords do not match");
				app_log("Passwords do not match",'info');
			}
			// Check Password Complexity
			elseif ($customer->password_strength($_REQUEST["password"]) < $GLOBALS['_config']->register->minimum_password_strength) {
				$page->addError("Password needs more complexity.");
				app_log("Complexity requirements ".$customer->password_strength($_REQUEST['password'])." < ".$GLOBALS['_config']->register->minimum_password_strength);
			}
			// If no errors and customer found, go ahead and update
			elseif ($page->errorCount() < 1 && $customer->id) {
				app_log("Updating customer ".$customer_id,'debug',__FILE__,__LINE__);
				if ($customer->changePassword($_REQUEST["password"])) {
					// set the user to active if they're expired, this will ensure then can continue to login
					if (in_array($customer->status,array('EXPIRED','BLOCKED'))) $customer->update(array('status' => 'ACTIVE'));

					if ($customer->error()) {
						app_log("Error updating customer: ".$customer->error,'error',__FILE__,__LINE__);
						$page->addError("Error updating customer password.  Our admins have been notified.  Please try again later");
					}
					else {
						// Send password reset notification email
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
						
						app_log("Expiring session and showing logged out page","info");
						if ($GLOBALS['_SESSION_']->end()) {
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
			else app_log("Incomplete requirements",'info');
		}
	}
	else {
		app_log("Viewing form, no submit");
	}
