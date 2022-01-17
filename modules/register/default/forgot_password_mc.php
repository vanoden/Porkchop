<?php
	$page = new \Site\Page();

	# Handle Actions
	if ($_REQUEST['email_address']) {
		# Check reCAPTCHA
		$url = "https://www.google.com/recaptcha/api/siteverify";
		$data = array(
			'secret'	=> $GLOBALS['_config']->captcha->private_key,
			'response'	=> $_REQUEST['g-recaptcha-response'],
			'remoteip'	=> $_SERVER['REMOTE_ADDR'],
		);
	
		$options = array(
			'http'	=> array(
				'method'	=> 'POST',
				'content'	=> http_build_query($data),
			),
		);
		# Don't need to store these fields
		unset($_REQUEST['g-recaptcha-response']);
		unset($_REQUEST['btn_submit']);

		$context = stream_context_create($options);
		$result = file_get_contents($url,false,$context);
		$captcha_success = json_decode($result);

		if ($captcha_success->success == true) {
			app_log('ReCAPTCHA OK','debug',__FILE__,__LINE__);

			if (valid_email($_REQUEST['email_address'])) {
				# Get User Info From Database
				$contact = new \Register\Contact();
				$contact->get('email',$_REQUEST['email_address']);
				if ($contact->error) {
					app_log("Error finding contact: ".$contact->error,'error',__FILE__,__LINE__);
					$page->addError("Error finding contact info, please try again later");
					return null;
				}

				if ($contact->person->id) {
					$customer = new \Register\Customer($contact->person->id);
					if ($customer->error) {
						app_log("Forgot Password Error: ".$customer->error,'error',__FILE__,__LINE__);
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
					if ($token->error) {
						app_log("Error generating password token: ".$token->error,'error',__FILE__,__LINE__);
						$page->addError("Error generating password token");
						return;
					}
					app_log("Generated password token '".$token->code."'",'debug',__FILE__,__LINE__);
					$recovery_url = "http";
					if ($GLOBALS['_config']->site->https) $recovery_url = "https";
					$recovery_url .= "://".$GLOBALS['_config']->site->hostname."/_register/login?token=".$token->code;

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
					app_log("Message: ".$notice_template->output(),'trace',__FILE__,__LINE__);

					# Build Message For Delivery
					$message = new \Email\Message();
					$message->html(true);
					$message->to($_REQUEST['email_address']);
					$message->from($GLOBALS['_config']->register->forgot_password->from);
					$message->subject($GLOBALS['_config']->register->forgot_password->subject);
					$message->body($notice_template->output());

					app_log("Sending Forgot Password Link",'debug',__FILE__,__LINE__);
					$transport = \Email\Transport::Create(array('provider' => $GLOBALS['_config']->email->provider));
					if (! $transport) {
						$page->addError("Error sending email, please contact us at ".$GLOBALS['_config']->site->support_email);
						return;
					}
					if ($transport->error()) {
						$page->addError("Error sending email, please contact us at ".$GLOBALS['_config']->site->support_email);
						app_log("Error initializing email transport: ".$transport->error(),'error',__FILE__,__LINE__);
						return;
					}
					$transport->hostname($GLOBALS['_config']->email->hostname);
					$transport->token($GLOBALS['_config']->email->token);
					$transport->deliver($message);
					if ($transport->error) {
						$page->addError("Error sending email, please contact us at service@spectrosinstruments.com");
						app_log("Error sending forgot password link: ".$transport->error,'error',__FILE__,__LINE__);
						return;
					}
				}
				else {
					app_log("Customer not found matching '".$_REQUEST['email_address']."', no email sent",'notice',__FILE__,__LINE__);
				}
				# Display Confirmation Page
				header("location: /_register/password_token_sent");
			}
			else {
				$page->addError("Sorry, but the email address you gave was not valid!");
			}
		}
		else {
			$page->addError("Sorry, CAPTCHA Invalid.  Please Try Again");
		}
	}

	function valid_email($email)
	{
		if (preg_match("/^[\w\-\_\.\+]+@[\w\-\_\.]+$/",$email)) return 1;
		else return 0;
	}
?>
