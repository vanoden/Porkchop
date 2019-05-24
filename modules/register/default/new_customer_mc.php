<?php
    /**
      * Register new potential customers
      *
      * @copyright Spectros Instruments
      * @author khinds
      */      
    $page = new \Site\Page();
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
	        if ($customer->password_strength($_REQUEST['password']) < $_GLOBALS['_config']->register->minimum_password_strength) {
		        $page->addError("Password not strong enough");
	        } elseif ($_REQUEST["password"] != $_REQUEST["password_2"]) {
		        $page->addError("Passwords do not match");
	        } else {

		        // Default Login to Email Address
		        if (! $_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['email_address'];

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
					        "login"				=> $_REQUEST['login'],
					        "password"			=> $_REQUEST['password'],
					        "first_name"		=> $_REQUEST['first_name'],
					        "last_name"			=> $_REQUEST['last_name'],
					        "validation_key"	=> $validation_key,
				        )
			        );
			        if ($customer->error) {
				        app_log("Error adding customer: ".$customer->error,'error',__FILE__,__LINE__);
				        $page->addError("Sorry, there was an error adding your account. Our admins have been notified. <br/>&nbsp;&nbsp;&nbsp;&nbsp;Please contact <a href='mailto:support@spectrosinstruments.com'>support@spectrosinstruments.com</a> if you have any futher issues.");
				        if (strpos($customer->error, 'Duplicate entry') !== false) {
				            $page->addError("Error: <strong>" . $_REQUEST['login'] . "</strong> has already been taken for a user name");
				            $page->loginTaken = true;
				        }
			        } else {

				        // Login New User by updating session
				        $GLOBALS['_SESSION_']->assign($customer->id);
				        if ($GLOBALS['_SESSION_']->error) $page->addError("Error updating session: ".$GLOBALS['_SESSION_']->error);

				        // Create Contact Record
				        if ($_REQUEST['work_email']) {
					        $customer->addContact(
						        array(
							        "type"			=> "email",
							        "description"	=> "Work Email",
							        "value"			=> $_REQUEST['work_email']
						        )
					        );
					        if ($customer->error) app_log("Error adding Work Email '".$_REQUEST['work_email']."': ".$customer->error,'error',__FILE__,__LINE__);
				        }
				        
				        if ($_REQUEST['home_email']) {
				        
					        // Create Contact Record
					        $customer->addContact(
						        array(
							        "person_id"		=> $customer->id,
							        "type"			=> "email",
							        "description"	=> "Home Email",
							        "value"			=> $_REQUEST['home_email']
						        )
					        );
					        
					        if ($customer->error) app_log("Error adding Home Email '".$_REQUEST['home_email']."': ".$customer->error,'error',__FILE__,__LINE__);
				        }
				        
				        if ($_REQUEST['phone']) {
				        
					        // Create Contact Record
					        $customer->addContact(
						        array(
							        "person_id"		=> $customer->id,
							        "type"			=> "phone",
							        "description"	=> "Business Phone",
							        "value"			=> $_REQUEST['phone']
						        )
					        );
					        
					        if ($customer->error) app_log("Error adding Business Phone '".$_REQUEST['phone']."': ".$customer->error,'error',__FILE__,__LINE__);
				        }

				        if ($_REQUEST['cell']) {
				        
					        // Create Contact Record
					        $customer->addContact(
						        array(
							        "person_id"		=> $customer->id,
							        "type"			=> "phone",
							        "description"	=> "Cell Phone",
							        "value"			=> $_REQUEST['cell']
						        )
					        );
					        
					        if ($customer->error) app_log("Error adding Cell Phone '".$_REQUEST['cell']."': ".$customer->error,'error',__FILE__,__LINE__);
				        }

                        // Initialize Register Queued Object
                        $queuedCustomer = new \Register\Queue();
                        $queuedCustomerData = array();
                        $queuedCustomerData['name'] = $_REQUEST['organization_name'];
                        $queuedCustomerData['code'] = time(); // @TODO, not sure about this column
                        $queuedCustomerData['is_reseller'] = 0;
                        $queuedCustomerData['assigned_reseller_id'] = NULL;
                        if (isset($_REQUEST['reseller']) && $_REQUEST['reseller'] == "yes") {
                            $queuedCustomerData['is_reseller'] = 1;
                            $queuedCustomerData['assigned_reseller_id'] = $_REQUEST['assigned_reseller_id'];
                        }
                        $queuedCustomerData['address'] = $_REQUEST['address'];
                        $queuedCustomerData['city'] = $_REQUEST['city'];
                        $queuedCustomerData['state'] = $_REQUEST['state'];
                        $queuedCustomerData['zip'] = $_REQUEST['zip'];
                        $queuedCustomerData['phone'] = $_REQUEST['phone'];
                        $queuedCustomerData['cell'] = $_REQUEST['cell'];                    
                        $queuedCustomerData['product_id'] = $_REQUEST['product_id'];
                        if (empty($queuedCustomerData['product_id'])) $queuedCustomerData['product_id'] = 0;
                        $queuedCustomerData['serial_number'] = $_REQUEST['serial_number'];
                        $queuedCustomerData['register_user_id'] = $customer->id;                           
                        $queuedCustomer->add($queuedCustomerData);
                        
                        if ($queuedCustomer->error) {
                            app_log("Error adding queued organization: ".$queuedCustomer->error,'error',__FILE__,__LINE__);
                            $page->addError("Sorry, there was an error adding your account. Our admins have been notified. <br/>&nbsp;&nbsp;&nbsp;&nbsp;Please contact <a href='mailto:support@spectrosinstruments.com'>support@spectrosinstruments.com</a> if you have any futher issues.");
							return;
                        }
                        
                        // create the verify account email
                        $emailNotification = new \Email\Notification(
                        array('subject' => 'Please verify your account',
                              'template' => BASE . '/modules/register/email_templates/verify_email.html',
                              'templateVars' => array('VERIFYING.URL' => 'https://'. $_config->site->hostname . '/_register/new_customer?method=verify&access=' . $validation_key . '&login=' . $_REQUEST['login'])
                              )
                        );
                        $isEmailSent = $emailNotification->send($_REQUEST['work_email'], 'no-reply@spectrosinstruments.com');
                    	if (!$isEmailSent) $page->addError("Confirmation email could not be sent, please contact us at support@spectrosinstruments.com to complete your registration, thank you!");

                    	// show thank you page
				        header("Location: /_register/thank_you");
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
                $emailNotification = new \Email\Notification(
                array('subject' => 'New verified customer - pending organizational approval', 
                      'template' => BASE . '/modules/register/email_templates/admin_notification.html', 
                      'templateVars' => array('ADMIN.URL' => 'https://'. $_config->site->hostname . '/_register/pending_customers', 'ADMIN.USERDETAILS' => $_REQUEST['login'])
                      )
                );  
                app_log("Sending Admin Confirm new customer reminder",'debug',__FILE__,__LINE__);
                $emailNotification->send('support@spectrosinstruments.com', 'no-reply@spectrosinstruments.com');
				$page->isVerifedAccount = true;
			}
			else {
				app_log("Key not matched",'notice');
				$page->addError("Invalid key");
			}
		}
		else {
			app_log("Login not matched",'notice');
			$page->addError("Invalid key");
		}
    }
