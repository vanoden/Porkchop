<?php
	/**
	  * Register new potential customers
	  *
	  * @copyright Spectros Instruments
	  * @author khinds
	  */      
	$page = new \Site\Page();
	$HTTPRequest = new \HTTP\Request();
	global $_config;

	$captcha_ok = false;

	// New Registration Submitted
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "register") {
		$customer = new \Register\Customer();
	    // Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
		if (!isset($_POST['csrfToken']) || ! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid request");
		}
		// CAPTCHA Bypass Key for Automated Testing
		elseif (!empty($GLOBALS['_config']->captcha->bypass_key) && !empty($_REQUEST['captcha_bypass_key']) && $GLOBALS['_config']->captcha->bypass_key == $_REQUEST['captcha_bypass_key']) {
			//Don't require catcha
			$captcha_ok = true;
		}
		// Check reCAPTCHA 2.0
		else {
			// CAPTCHA Required and Provided
			$reCAPTCHA = new \GoogleAPI\ReCAPTCHA();
			if ($reCAPTCHA->test($customer,$_REQUEST['g-recaptcha-response'])) {
				app_log('ReCAPTCHA OK','debug',__FILE__,__LINE__);
			$captcha_ok = true;
			}
			else {
				$captcha_ok = false;
			}
		}

		if ($captcha_ok && ! $page->errorCount()) {
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
			$contact = new \Register\Contact();
			if ($customer->password_strength($_REQUEST['password']) < $GLOBALS['_config']->register->minimum_password_strength) {
				$page->addError("Password not strong enough");
			}
			elseif ($_REQUEST["password"] != $_REQUEST["password_2"]) {
				$page->addError("Passwords do not match");
			}
			elseif (! $customer->validLogin($_REQUEST['login'])) {
				$page->addError("Invalid login");
			}
			// Make Sure Login is unique
			elseif ($customer->get($_REQUEST['login'])) {
				$page->addError("Sorry, login already taken");
				$_REQUEST['login'] = '';
			}
			elseif (!empty($customer->error())) {
				$page->addError("Error checking login: ".$customer->error());
			}
			elseif (!empty($_REQUEST['phone_number']) && ! $contact->validValue('phone',$_REQUEST['phone_number'])) {
				$page->addError("Invalid Phone Number");
			}
			elseif (!empty($_REQUEST['email_address']) && ! $contact->validValue('email',$_REQUEST['email_address'])) {
				$page->addError("Invalid Email Address");
			}
			elseif (empty($_REQUEST['email_address']) && empty($_REQUEST['phone_number'])) {
				$page->addError("Must provide an email address or phone number");
			}
			elseif (!$country->exists()) {
				$page->addError("Must select a country");
			}
			elseif (!$province->exists()) {
				$page->addError("Must Select a State or Province");
			}
			else {
				// Generate Validation Key
				$validation_key = md5(microtime());

				$loginTaken = false;

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
					if (strpos($customer->error(), 'Duplicate entry') !== false) {
						$page->addError("Error: <strong>" . $_REQUEST['login'] . "</strong> has already been taken for a user name");
						$loginTaken = true;
					}
				}
				// Store Contact Info and Deliver Address Verification Email
				else {
					if ($_REQUEST['email_address']) {
						$customer->addContact(
							array(
								"type"			=> "email",
								"description"	=> $_REQUEST['email_type'],
								"value"			=> $_REQUEST['email_address'],
								"notify"		=> 1
							)
						);
						if ($customer->error()) app_log("Error adding Email Address'".$_REQUEST['email_address']."': ".$customer->error(),'error',__FILE__,__LINE__);
						else app_log("Added address '".$_REQUEST['email_address']."' for customer ".$customer->code,'info');
					}
					else app_log("No email address provided",'warning');

					if (isset($_REQUEST['phone_number'])) {
						// Create Contact Record
						$customer->addContact(
							array(
								"type"			=> "phone",
								"description"	=> $_REQUEST['phone_type'],
								"value"			=> $_REQUEST['phone_number']
							)
						);

						if ($customer->error()) app_log("Error adding Phone Number '".$_REQUEST['phone_number']."': ".$customer->error(),'error',__FILE__,__LINE__);
						else app_log("Added phone '".$_REQUEST['phone_number']."' for customer ".$customer->code,'info');
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

					if ($queuedCustomer->error()) {
						app_log("Error adding queued organization: ".$queuedCustomer->error(),'error',__FILE__,__LINE__);
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
								'VERIFYING.URL' => $verify_url,
								'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? ''
							)
						)
					);
					if ($template->error()) {
						app_log($template->error(),'error');
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
		elseif (! $page->errorCount()) {
		   $captcha_ok = false;
		   $page->addError("Please confirm your humanity, solve captcha below.");
		}
	}

	// Resend Email Verification Email
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "resend") {
	    // Anti-CSRF measures, reject an HTTP POST with invalid/missing token in session
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid request");
		}
		else {
            // Generate New Validation Key
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
			                'VERIFYING.URL' => $verify_url,
			                'COMPANY.NAME' => $GLOBALS['_SESSION_']->company->name ?? 'Spectros Instruments'
		                )
	                )
                );
                if ($template->error()) {
	                app_log($template->error(),'error');
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
			else {
                $page->addError("Account not found.");
            }
		}
	}

	// Email Verification - only process if NOT an AJAX request (AJAX handled by API)
	// Set flag to indicate we should show verification UI
	$showVerificationUI = false;
	$verificationLogin = null;
	$verificationAccess = null;
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "verify" && !isset($_REQUEST['ajax'])) {
		// For non-AJAX requests, just pass the parameters to the view
		$showVerificationUI = true;
		$verificationLogin = $_REQUEST['login'] ?? null;
		$verificationAccess = $_REQUEST['access'] ?? null;
	}

	// Load Data to Populate Form Inputs
	$resellerList = new \Register\OrganizationList();
	$resellers = $resellerList->find(array("is_reseller" => true));

	$productList = new \Product\ItemList();
	$productsAvailable = $productList->find(array('type' => 'unique','status' => 'active'));

	$countryList = new \Geography\CountryList();
	$countries = $countryList->find(array("default" => "United States of America"));

	if (empty($_REQUEST['country_id'])) $_REQUEST['country_id'] = $countries[0]->id;
	$provinceList = new \Geography\ProvinceList();
	$provinces = $provinceList->find(array("country_id" => $_REQUEST['country_id']));

	if (!isset($_REQUEST['province_id'])) $_REQUEST['province_id'] = 0;
