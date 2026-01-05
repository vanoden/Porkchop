<?php
	$site = new \Site();
	$page = $site->page();

	if (empty($_REQUEST['g-captcha-response'])) $_REQUEST['g-captcha-response'] = '';
	if (empty($_REQUEST['csrfToken'])) $_REQUEST['csrfToken'] = '';

	# Handle Actions
	if (!empty($_REQUEST['email_address'])) {
		$customer = new \Register\Customer();
		// CAPTCHA Required and Provided
		$reCAPTCHA = new \GoogleAPI\ReCAPTCHA();
		if ($reCAPTCHA->test($customer,$_REQUEST['g-recaptcha-response'])) {
			app_log('ReCAPTCHA OK','debug',__FILE__,__LINE__);

			if (!isset($_POST['csrfToken']) or !strlen($_POST['csrfToken'])) {
				$page->addError("Invalid Request");
				app_log("Invalid Request: CSRF Token not send by browser",'notice');
			}
			elseif (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
				$page->addError("Invalid Request");
				app_log("Invalid Request: ".$_POST['csrfToken']." ne ".$GLOBALS['_SESSION_']->getCSRFToken(),'notice');
			}
			elseif (valid_email($_REQUEST['email_address'])) {
				# Get User Info From Database
				$contact = new \Register\Contact();
				if (!empty($_REQUEST['email_address'])) {
					$contact->getContact('email',$_REQUEST['email_address']);
					if ($contact->error()) {
						app_log("Error finding contact: ".$contact->error(),'error',__FILE__,__LINE__);
						$page->addError("Error finding contact info, please try again later");
						return null;
					}

					if ($contact->person->id) {
						$customer = new \Register\Customer($contact->person->id);
						if ($customer->error()) {
							app_log("Forgot Password Error: ".$customer->error(),'error',__FILE__,__LINE__);
							$page->addError("Form error");
							return;
						}
						if (! $customer) {
							app_log("Forgot Password Error: Customer not found",'error',__FILE__,__LINE__);
							$page->addError("Form error");
							return;
						}
						app_log("Identified customer for email ".$_REQUEST['email_address'],'debug',__FILE__,__LINE__);
			
						# Make Sure the Customer Has a Real Login
						if (! isset($customer->code) or ! strlen($customer->code)) {
							app_log("Customer has invalid login",'error',__FILE__,__LINE__);
							$page->addError("Sorry, you have not been given access to this site.");
							return;
						}
			
						# Generate a Password Recovery Token
						$token = new \Register\PasswordToken();
						$token->add($customer->id);
						if ($token->error()) {
							app_log("Error generating password token: ".$token->error(),'error',__FILE__,__LINE__);
							$page->addError("Error generating password token");
							return;
						}
						app_log("Generated password token '".$token->code."'",'debug',__FILE__,__LINE__);
						$recovery_url = "http";
						if ($GLOBALS['_config']->site->https) $recovery_url = "https";
						$recovery_url .= "://".$GLOBALS['_config']->site->hostname."/_register/reset_password?token=".$token->code;

						// Audit the Add Password Token Event
						$audit = new \Site\AuditLog\Event();
						$audit->add(array(
							'instance_id' => $customer->id,
							'class_name' => 'Register\Customer',
							'description' => 'Password Recovery Token Created'
						));

						###############################################
						### Password Found, Generate Recovery Email	###
						###############################################
						# Send Link
						if (! file_exists($GLOBALS['_config']->register->forgot_password->template)) {
							app_log("Template '".$GLOBALS['_config']->register->forgot_password->template."' not found",'error',__FILE__,__LINE__);
							$page->addError("Template '".$GLOBALS['_config']->register->forgot_password->template."' not found");
							return;
						}
						try {
							$notice_content = file_get_contents($GLOBALS['_config']->register->forgot_password->template);
						}
						catch (Exception $e) {
							app_log("Email template load failed: ".$e->getMessage(),'error',__FILE__,__LINE__);
							$page->addError("Template load failed.  Try again later");
							return;
						}

						$notice_template = new \Content\Template\Shell();
						$notice_template->content($notice_content);
						$notice_template->addParam('RECOVERY.URL',$recovery_url);
						$notice_template->addParam('COMPANY.NAME',$GLOBALS['_SESSION_']->company->name ?? '');
						app_log("Message: ".$notice_template->output(),'trace',__FILE__,__LINE__);

						# Build Message For Delivery
						$message = new \Email\Message();
						$message->html(true);
						$message->to($_REQUEST['email_address']);
						$message->from($GLOBALS['_config']->register->forgot_password->from);
						$message->subject($GLOBALS['_config']->register->forgot_password->subject);
						$message->body($notice_template->output());

					app_log("Sending Forgot Password Link to ".$_REQUEST['email_address'],'debug',__FILE__,__LINE__);
					$transportFactory = new \Email\Transport();
					$transport = $transportFactory->Create(array('provider' => $GLOBALS['_config']->email->provider));
					if (! $transport) {
						app_log("Failed to create email transport with provider: ".$GLOBALS['_config']->email->provider,'error',__FILE__,__LINE__);
						$page->addError("Error sending email, please contact us at ".$GLOBALS['_config']->site->support_email);
						return;
					}
					if ($transport->error()) {
						app_log("Error initializing email transport: ".$transport->error(),'error',__FILE__,__LINE__);
						$page->addError("Error sending email, please contact us at ".$GLOBALS['_config']->site->support_email);
						return;
					}
					$transport->hostname($GLOBALS['_config']->email->hostname);
					$transport->token($GLOBALS['_config']->email->token);
					app_log("Email transport configured: provider=".$GLOBALS['_config']->email->provider.", hostname=".$GLOBALS['_config']->email->hostname,'debug',__FILE__,__LINE__);
					if ($transport->deliver($message)) {
						$result = $transport->result();
						app_log("Email delivery successful. Transport result: ".$result." for email to ".$_REQUEST['email_address'],'info',__FILE__,__LINE__);

						// Terminate Session
						$GLOBALS['_SESSION_']->end();

						// Display Confirmation Page
						header("location: /_register/password_token_sent");
						exit;
					}
					elseif ($transport->error()) {
						app_log("Error sending forgot password link: ".$transport->error()." for email to ".$_REQUEST['email_address'],'error',__FILE__,__LINE__);
						$page->addError("Error sending email, please contact us at service@spectrosinstruments.com");
						return;
					}
					else {
						app_log("Email delivery failed without error message. Transport result: ".$transport->result()." for email to ".$_REQUEST['email_address'],'error',__FILE__,__LINE__);
						$page->addError("Error sending email, please contact us at service@spectrosinstruments.com");
						return;
					}
					}
					else {
						app_log("Customer not found matching '".$_REQUEST['email_address']."', no email sent",'notice',__FILE__,__LINE__);

						// Send them to complete page anyway so they can't use this to test email addresses
						// Display Confirmation Page
						header("location: /_register/password_token_sent");
						exit;
					}
				}
				else {
					$page->addError("Sorry, but the email address you gave was not valid!");
				}
			}
		}
		else {
			$page->addError("Sorry, CAPTCHA Invalid.  Please Try Again");
		}
	}
