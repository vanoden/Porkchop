<?php
	/**
	  * Register new potential customers
	  *
	  * @copyright Spectros Instruments
	  * @author khinds
	  */      
	$page = new \Site\Page();
	$HTTPRequest = new \HTTP\Request();
	$resellerList = new \Register\OrganizationList();
	$productList = new \Product\ItemList();
	$productsAvailable = $productList->find(array('type' => 'unique','status' => 'active'));
	$resellers = $resellerList->find(array("is_reseller" => true));
	$page->captchaPassed = true;
	global $_config;
	
	// handle form submit	
	if ($_REQUEST['method'] == "register") {
	
		// Check reCAPTCHA 2.0
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
		
		// Don't need to store these fields
		unset($_REQUEST['g-recaptcha-response']);
		unset($_REQUEST['btn_submit']);

		$context = stream_context_create($options);
		$result = file_get_contents($url, false, $context);
		$captcha_success = json_decode($result);
		
		// $captcha_success = new stdClass();
		// $captcha_success->success = true;
		
		if ($captcha_success->success == true) {
		
			// Initialize Customer Object
			$customer = new \Register\Customer();
			if ($customer->password_strength($_REQUEST['password']) < $GLOBALS['_config']->register->minimum_password_strength) {
				$page->addError("Password not strong enough");
			} elseif ($_REQUEST["password"] != $_REQUEST["password_2"]) {
				$page->addError("Passwords do not match");
			} else {

				// Generate Validation Key
				$validation_key = md5(microtime());

				// Make Sure Login is unique
				if ($customer->get($_REQUEST['login'])) {
					$page->addError("Sorry, login already taken");
					$_REQUEST['login'] = '';
				} elseif ($customer->error) {
					$page->addError("Error checking login: ".$customer->error);
				} else {
					$page->loginTaken = false;
					// Add Customer Record to Database
					$customer->add(
						array(
							"login"				=> $HTTPRequest->cleanCharacters($_REQUEST['login']),
							"password"			=> $HTTPRequest->cleanCharacters($_REQUEST['password']),
							"first_name"		=> $HTTPRequest->cleanCharacters($_REQUEST['first_name']),
							"last_name"			=> $HTTPRequest->cleanCharacters($_REQUEST['last_name']),
							"validation_key"	=> $validation_key,
						)
					);
					
					if ($customer->error) {
						app_log("Error adding customer: ".$customer->error,'error',__FILE__,__LINE__);
						$page->addError("Sorry, there was an error adding your account. Our admins have been notified. <br/>&nbsp;&nbsp;&nbsp;&nbsp;Please contact <a href='mailto:".$GLOBALS['_config']->site->support_email."'>".$GLOBALS['_config']->site->support_email."</a> if you have any futher issues.",'error');
						if (strpos($customer->error, 'Duplicate entry') !== false) {
							$page->addError("Error: <strong>" . $_REQUEST['login'] . "</strong> has already been taken for a user name");
							$page->loginTaken = true;
						}
					} else {
						// Create Contact Record
						if ($_REQUEST['email_address']) {
							$customer->addContact(
								array(
									"type"			=> "email",
									"description"	=> $HTTPRequest->cleanCharacters($_REQUEST['email_type']),
									"value"			=> $HTTPRequest->cleanCharacters($_REQUEST['email_address']),
									"notify"		=> 1
								)
							);
							if ($customer->error) app_log("Error adding Email Address'".$_REQUEST['email_address']."': ".$customer->error,'error',__FILE__,__LINE__);
							else app_log("Added address '".$_REQUEST['email_address']."' for customer ".$customer->login,'info');
						}
						else app_log("No email address provided",'warning');

						if ($_REQUEST['phone_number']) {
							// Create Contact Record
							$customer->addContact(
								array(
									"type"			=> "phone",
									"description"	=> $HTTPRequest->cleanCharacters($_REQUEST['phone_type']),
									"value"			=> $HTTPRequest->cleanCharacters($_REQUEST['phone_number'])
								)
							);

							if ($customer->error) app_log("Error adding Phone Number '".$_REQUEST['phone_number']."': ".$customer->error,'error',__FILE__,__LINE__);
							else app_log("Added phone '".$_REQUEST['phone_number']."' for customer ".$customer->login,'info');
						}
						else app_log("No phone number provided",'warning');

						// Initialize Register Queued Object
						$queuedCustomer = new \Register\Queue();
						$queuedCustomerData = array();
						$queuedCustomerData['name'] = $HTTPRequest->cleanCharacters($_REQUEST['organization_name']);
						$queuedCustomerData['code'] = time(); // @TODO, not sure about this column
						$queuedCustomerData['is_reseller'] = 0;
						$queuedCustomerData['assigned_reseller_id'] = NULL;
						if (isset($_REQUEST['reseller']) && $_REQUEST['reseller'] == "yes") {
							$queuedCustomerData['is_reseller'] = 1;
							$queuedCustomerData['assigned_reseller_id'] = $HTTPRequest->cleanCharacters($_REQUEST['assigned_reseller_id']);
						}
						$queuedCustomerData['address'] = $HTTPRequest->cleanCharacters($_REQUEST['address']);
						$queuedCustomerData['city'] = $HTTPRequest->cleanCharacters($_REQUEST['city']);
						$queuedCustomerData['state'] = $HTTPRequest->cleanCharacters($_REQUEST['state']);
						$queuedCustomerData['zip'] = $HTTPRequest->cleanCharacters($_REQUEST['zip']);
						$queuedCustomerData['phone'] = $HTTPRequest->cleanCharacters($_REQUEST['phone']);
						$queuedCustomerData['cell'] = $HTTPRequest->cleanCharacters($_REQUEST['cell']);
						$queuedCustomerData['product_id'] = $HTTPRequest->cleanCharacters($_REQUEST['product_id']);
						if (empty($queuedCustomerData['product_id'])) $queuedCustomerData['product_id'] = 0;
						$queuedCustomerData['serial_number'] = $HTTPRequest->cleanCharacters($_REQUEST['serial_number']);
						$queuedCustomerData['register_user_id'] = $customer->id;                           
						$queuedCustomer->add($queuedCustomerData);
						
						if ($queuedCustomer->error) {
							app_log("Error adding queued organization: ".$queuedCustomer->error,'error',__FILE__,__LINE__);
							$page->addError("Sorry, there was an error adding your account. Our admins have been notified. <br/>&nbsp;&nbsp;&nbsp;&nbsp;Please contact <a href='mailto:".$GLOBALS['_config']->site->support_email."'>".$GLOBALS['_config']->site->support_email."</a> if you have any futher issues.");
							return;
						}
						
						// create the verify account email
						$verify_url = $_config->site->hostname . '/_register/new_customer?method=verify&access=' . $validation_key . '&login=' . $_REQUEST['login'];
						if ($_config->site->https) $verify_url = "https://$verify_url";
						else $verify_url = "http://$verify_url";
						$template = new \Content\Template\Shell(
							array(
								'path'	=> $_config->register->verify_email->template,
								'parameters'	=> array(
									'VERIFYING.URL' => $verify_url
								)
							)
						);
						if ($template->error()) {
							$page->addError("Error generating verification email, please contact us at ".$_config->site->support_email." to complete your registration, thank you!");
						}
						else {
							$message = new \Email\Message($_config->register->verify_email);
							$message->html(true);
							$message->body($template->output());
							if (! $customer->notify($message)) {
								$page->addError("Confirmation email could not be sent, please contact us at ".$_config->site->support_email." to complete your registration, thank you!");
								app_log("Error sending confirmation email: ".$customer->error(),'error');
							} else {
								// show thank you page
								header("Location: /_register/thank_you");
							}
						}
					}
				}
			}
		} else {
		   $page->captchaPassed = false;
		   $page->addError("Please confirm your humanity, solve captcha below.");
		}
	}

	if ($_REQUEST['method'] == "verify") {
		app_log("Verifying customer ".$_REQUEST['login']." with key ".$_REQUEST['access'],'notice');
		
		// Initialize Customer Object
		$page->isVerifedAccount = false;
		$customer = new \Register\Customer();
		if ($customer->get($_REQUEST['login'])) {
			app_log("Found customer ".$customer->id);
			if ($customer->verify_email($_REQUEST['access'])) {
			
				// update the queued organization to "PENDING" because the email has been verifed
				app_log("Validation key confirmed, updating queue record");
				$queuedCustomer = new \Register\Queue(); 
				$queuedCustomer->getByQueuedLogin($customer->id);
				$queuedCustomer->update (array('status'=>'PENDING'));

				// create the notify support reminder email for the new verified customer
				app_log("Generating notification email");
				$url = $_config->site->hostname . '/_register/pending_customers';
				if ($_config->site->https) $url = "https://$url";
				else $url = "http://$url";

				$template = new \Content\Template\Shell(
					array(
						'path'	=> $_config->registration_notification->template,
						'parameters'	=> array(
							'ADMIN.URL' 		=> $url,
							'ADMIN.USERDETAILS'	=> $_REQUEST['login']
						)
					)
				);

				$message = new \Email\Message($GLOBALS['_config']->register->registration_notification);
				$message->html(true);
				$message->body($template->output());

				app_log("Sending Admin Confirm new customer reminder",'debug');
				$role = new \Register\Role();
				$role->get('register manager');
				$role->notify($message);
				if ($role->error) app_log("Error sending admin confirm new customer reminder: ".$role->error);				

				$page->isVerifedAccount = true;				
			} else {
				app_log("Key not matched",'notice');
				$page->addError("Invalid key");
			}
		}
		else {
			app_log("Login not matched",'notice');
			$page->addError("Invalid key");
		}
	}
