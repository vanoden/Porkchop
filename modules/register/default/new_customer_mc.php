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
	
    // Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
	if (isset($_POST) && !empty($_POST) && ! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
		$page->addError("Invalid request");
		return 403;
	}
	
	// handle form submit	
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "register") {
	
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
		
		if ($captcha_success->success == true) {
			// Country is US.  NEEDS TO BE FIXED!!!

			// Clean Up Input
			$_REQUEST['login'] = trim($_REQUEST['login']);
			$_REQUEST['first_name'] = noXSS(trim($_REQUEST['first_name']));
			$_REQUEST['last_name'] = noXSS(trim($_REQUEST['last_name']));
			$_REQUEST['organization_name'] = noXSS(trim($_REQUEST['organization_name']));
			$_REQUEST['address'] = noXSS(trim($_REQUEST['address']));
			$_REQUEST['city'] = noXSS(trim($_REQUEST['city']));
			$_REQUEST['zip'] = noXSS(trim($_REQUEST['zip']));
			$country = new \Geography\Country($_REQUEST['country_id']);
			$province = new \Geography\Province($_REQUEST['province_id']);

			// Initialize Customer Object
			$customer = new \Register\Customer();
			if ($customer->password_strength($_REQUEST['password']) < $GLOBALS['_config']->register->minimum_password_strength) {
				$page->addError("Password not strong enough");
			}
			elseif ($_REQUEST["password"] != $_REQUEST["password_2"]) {
				$page->addError("Passwords do not match");
			}
			else {
				// Generate Validation Key
				$validation_key = md5(microtime());

				// Make Sure Login is unique
				if (! $customer->validLogin($_REQUEST['login'])) {
					$page->addError("Invalid login");
				}
				elseif ($customer->get($_REQUEST['login'])) {
					$page->addError("Sorry, login already taken");
					$_REQUEST['login'] = '';
				}
				elseif ($customer->error) {
					$page->addError("Error checking login: ".$customer->error);
				}
				elseif (!empty($_REQUEST['phone_number']) && ! preg_match('/^[\d\-\.\+\_\s]+$/',$_REQUEST['phone_number'])) {
					$page->addError("Invalid Phone Number");
				}
				elseif (!empty($_REQUEST['email_address']) && ! preg_match('/^[\w\-\.]+\@[\w\.\-]+$/',$_REQUEST['email_address'])) {
					$page->addError("Invalid Email Address");
				}
				else {
					$page->loginTaken = false;

					// Add Customer Record to Database
					$customer->add(
						array(
							"login"				=> $_REQUEST['login'],
							"password"			=> $_REQUEST['password'],
							"first_name"		=> $_REQUEST['first_name'],
							"last_name"			=> $_REQUEST['last_name'],
							"validation_key"	=> $validation_key,
						)
					);

					if ($customer->error()) {
						app_log("Error adding customer: ".$customer->error(),'error',__FILE__,__LINE__);
						$page->addError("Sorry, there was an error adding your account. Our admins have been notified. <br/>&nbsp;&nbsp;&nbsp;&nbsp;Please contact <a href='mailto:".$GLOBALS['_config']->site->support_email."'>".$GLOBALS['_config']->site->support_email."</a> if you have any futher issues.",'error');
						if (strpos($customer->error, 'Duplicate entry') !== false) {
							$page->addError("Error: <strong>" . $_REQUEST['login'] . "</strong> has already been taken for a user name");
							$page->loginTaken = true;
						}
					}
					else {
						// Create Contact Record
						if ($_REQUEST['email_address']) {
							$customer->addContact(
								array(
									"type"			=> "email",
									"description"	=> $_REQUEST['email_type'],
									"value"			=> noXSS($_REQUEST['email_address']),
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
									"description"	=> $_REQUEST['phone_type'],
									"value"			=> noXSS($_REQUEST['phone_number'])
								)
							);

							if ($customer->error) app_log("Error adding Phone Number '".$_REQUEST['phone_number']."': ".$customer->error,'error',__FILE__,__LINE__);
							else app_log("Added phone '".$_REQUEST['phone_number']."' for customer ".$customer->login,'info');
						}
						else app_log("No phone number provided",'warning');

						// Initialize Register Queued Object
						$queuedCustomer = new \Register\Queue();
						$queuedCustomerData = array();
						$queuedCustomerData['name'] = $_REQUEST['organization_name'];
						$queuedCustomerData['code'] = time();
						$queuedCustomerData['is_reseller'] = 0;
						$queuedCustomerData['assigned_reseller_id'] = NULL;
						if (isset($_REQUEST['reseller']) && $_REQUEST['reseller'] == "yes") {
							$queuedCustomerData['is_reseller'] = 1;
							$queuedCustomerData['assigned_reseller_id'] = $_REQUEST['assigned_reseller_id'];
						}
						$queuedCustomerData['address'] = $_REQUEST['address'];
						$queuedCustomerData['city'] = $_REQUEST['city'];
						$queuedCustomerData['state'] = $province->name;
						$queuedCustomerData['country'] = $country->name;
						$queuedCustomerData['zip'] = $_REQUEST['zip'];
						$queuedCustomerData['phone'] = $_REQUEST['phone_number'];
						$queuedCustomerData['product_id'] = $_REQUEST['product_id'];
						if (empty($queuedCustomerData['product_id'])) $queuedCustomerData['product_id'] = 0;
						$queuedCustomerData['serial_number'] = $_REQUEST['serial_number'];
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
							app_log($template->error(),'error');
							$page->addError("Error generating verification email, please contact us at ".$_config->site->support_email." to complete your registration, thank you!");
						} else {
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
	
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "resend") {
	
            // Generate Validation Key
		    $validation_key = md5(microtime());
            $customer = new \Register\Customer();
            $verifiedCustomer = $customer->get($_REQUEST['login']);
            if ($verifiedCustomer) {
            
                $customer->update(array('validation_key' => $validation_key));
            
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
	                app_log($template->error(),'error');
	                $page->addError("Error generating verification email, please contact us at ".$_config->site->support_email." to complete your registration, thank you!");
                } else {
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
            } else {
                $page->addError("Account not found.");
            }
	}
	
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "verify") {
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
				
				if ($queuedCustomer->status == "VERIFYING") $queuedCustomer->update (array('status'=>'PENDING'));

				// create the notify support reminder email for the new verified customer
				app_log("Generating notification email");
				$url = $_config->site->hostname . '/_register/pending_customers';
				if ($_config->site->https) $url = "https://$url";
				else $url = "http://$url";

				$template = new \Content\Template\Shell($_config->register->registration_notification->template);
				$template->addParams(array(
						'ORGANIZATION.NAME'		=> $queuedCustomer->organization,
						'CUSTOMER.FIRST_NAME'	=> $customer->first_name,
						'CUSTOMER.LAST_NAME'	=> $customer->last_name,
						'EMAIL'					=> $queuedCustomer->email_address,
						'CUSTOMER.LOGIN'		=> $customer->login,
						'SITE.LINK'				=> 'http://'.$_config->site->hostname.'/_register/pending_customers'
					)
				);

				$message = new \Email\Message($_config->register->registration_notification);
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

	$countryList = new \Geography\CountryList();
	$countries = $countryList->find(array("default" => "United States of America"));

	if (empty($_REQUEST['country_id'])) $_REQUEST['country_id'] = $countries[0]->id;
	$provinceList = new \Geography\ProvinceList();
	$provinces = $provinceList->find(array("country_id" => $_REQUEST['country_id']));
