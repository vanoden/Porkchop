<?php
	$page = new \Site\Page();

	# Handle Submit
	if ($_REQUEST['btn_submit']) {
		app_log('Contact form submitted by '.$_REQUEST['first_name'].' '.$_REQUEST['last_name'].' <'.$_REQUEST['email_address'].'>','notice',__FILE__,__LINE__);

		# Check reCAPTCHA
		$url = "http://www.google.com/recaptcha/api/verify";
		$data = array(
			'privatekey'	=> $GLOBALS['_config']->captcha->private_key,
			'remoteip'		=> $_SERVER['REMOTE_ADDR'],
			'challenge'		=> $_REQUEST['recaptcha_challenge_field'],
			'response'		=> $_REQUEST['recaptcha_response_field']
		);

		$options = array(
			'http'	=> array(
				'header'	=> "Content-type: application/x-www-form-urlencoded\r\n",
				'method'	=> 'POST',
				'content'	=> http_build_query($data),
			),
		);

		$context = stream_context_create($options);
		$result = file_get_contents($url,false,$context);

		if (preg_match('/^true/',$result))
		{
			app_log('ReCAPTCHA OK','debug',__FILE__,__LINE__);

			# Don't need to store these fields
			unset($_REQUEST['recaptcha_challenge_field']);
			unset($_REQUEST['recaptcha_response_field']);
			unset($_REQUEST['btn_submit']);

			# Store Data
			$event = new \Contact\Event();
			if ($event->error) {
				app_log("Error initializing ContactEvent: ".$event->error,'error',__FILE__,__LINE__);
				$page->addError("Sorry, there was an error submitting the contact form.  Please call.");
			}
			else {
				$event->add($_REQUEST);
				if ($event->error) {
					app_log("Error submitting ContactEvent: ".$event->error,'error',__FILE__,__LINE__);
					$page->addError("Sorry, there was an error submitting the contact form.  Please call.");
				}
				else {
					app_log("Contact Form Submitted: ".print_r($form_data,true),'notice',__FILE__,__LINE__);

					# Notify Admins
					$message_body  = "Contact Form Submitted on ".date('m/d/Y H:i')."<br>\r\n";
					$message_body .= "<table>\r\n";
					foreach(array_keys($_REQUEST) as $field) {
						$message_body .= "<tr><th>$field</th><td>".$_REQUEST[$field]."</td></tr>";
					}
					$message_body .= "</table>";
					$message = array(
						"from"		=> "contact_form@".$GLOBALS['_SESSION_']->domain,
						"subject" 	=> "Contact Form Submitted",
						"body" 		=> $message_body
					);
					$role = new \Register\Role();
					$role->notify("contact admin",$message);
					if ($role->error) {
						$page->addError("Sorry, I was unable to contact representatives.  Please call.");
						app_log("Error notifying role members: ".$role->error,'error',__FILE__,__LINE__);
					}
					else {
						# Display Confirmation Page
						header("location: /_contact/thankyou");
						exit;
					}
				}
			}
		}
		else
		{
			app_log("reCaptcha failed: $result",'notice',__FILE__,__LINE__);
			print "Error submitting form: ".$result;
		}
	}
	else
		app_log('Contact Form Loaded from '.$_SERVER['HTTP_X_FORWARDED_FOR'],'debug',__FILE__,__LINE__);
