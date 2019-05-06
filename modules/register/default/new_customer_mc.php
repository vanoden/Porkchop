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
                        // @TODO, let's get a global function going for emailing people by "template files " / "template placeholder values"
                        $welcomeEmailTemplate = BASE. '/modules/register/email_templates/verify_email.html';
                    	if (! file_exists($welcomeEmailTemplate)) {
                    		app_log("Template '".$welcomeEmailTemplate."' not found",'error',__FILE__,__LINE__);
                    		$page->addError("Template '".$welcomeEmailTemplate."' not found");
                    		return;
                    	}
                    	try {
                    		$verifyContent = file_get_contents($welcomeEmailTemplate);
                    	} catch (Exception $e) {
                    		app_log("Email template load failed: ".$e->getMessage(),'error',__FILE__,__LINE__);
                    		$page->addError("Template load failed.  Try again later");
                    		return;
                    	}
                    	$verifyTemplate = new \Content\Template\Shell();
                    	$verifyTemplate->content($verifyContent);
                    	$verifyTemplate->addParam('VERIFYING.URL', 'https://'. $_config->site->hostname . '/_register/new_customer?method=verify&access=' . $validation_key . '&login=' . $_REQUEST['login']);
                    	app_log("Message: ".$verifyTemplate->output(),'trace',__FILE__,__LINE__);

                    	// Build Message For Delivery
                    	$message = new \Email\Message();
                    	$message->html(true);
                    	$message->to($_REQUEST['work_email']);
                    	$message->from('no-reply@spectrosinstruments.com');
                    	$message->subject('Please verify your account');
                    	$message->body($verifyTemplate->output());

                    	app_log("Sending Verify Email Link",'debug',__FILE__,__LINE__);
                    	$transport = \Email\Transport::Create(array('provider' => $GLOBALS['_config']->email->provider));
                    	$transport->hostname($GLOBALS['_config']->email->hostname);
                    	$transport->token($GLOBALS['_config']->email->token);
                    	$transport->deliver($message);
                    	if ($transport->error) {
                    		$page->addError("Error sending email, please contact us at service@spectrosinstruments.com");
                    		app_log("Error sending forgot password link: ".$transport->error,'error',__FILE__,__LINE__);
                    		return;
                    	}
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
    
		// Initialize Customer Object
		$page->isVerifedAccount = false;
		$customer = new \Register\Customer();
        $customer->getAllDetails($_REQUEST['login']);
        
        if ($customer['validation_key'] == $_REQUEST['access']) {
            
            // update the queued organization to "PENDING" because the email has been verifed
            $queuedCustomer = new \Register\Queue(); 
            $queuedCustomer->getByQueuedLogin($customer['id']);
            $queuedCustomer->update (array('status'=>'PENDING'));
            
            // create the notify support reminder email for the new verified customer
            // @TODO, let's get a global function going for emailing people by "template files " / "template placeholder values"
            $adminReminderTemplate = BASE . '/modules/register/email_templates/admin_notification.html';
        	if (! file_exists($adminReminderTemplate)) {
        		app_log("Template '".$adminReminderTemplate."' not found",'error',__FILE__,__LINE__);
        		$page->addError("Template '".$adminReminderTemplate."' not found");
        		return;
        	}
        	try {
        		$verifyContent = file_get_contents($adminReminderTemplate);
        	} catch (Exception $e) {
        		app_log("Email template load failed: ".$e->getMessage(),'error',__FILE__,__LINE__);
        		$page->addError("Template load failed. Try again later");
        		return;
        	}
        	$verifyTemplate = new \Content\Template\Shell();
        	$verifyTemplate->content($verifyContent);
        	$verifyTemplate->addParam('ADMIN.URL', 'https://'. $_config->site->hostname . '/_register/pending_customers');
        	$verifyTemplate->addParam('ADMIN.USERDETAILS', $_REQUEST['login']);
        	
        	app_log("Message: ".$verifyTemplate->output(),'trace',__FILE__,__LINE__);

        	// Build Message For Delivery
        	$message = new \Email\Message();
        	$message->html(true);
        	$message->to('support@spectrosinstruments.com');
        	$message->from('no-reply@spectrosinstruments.com');
        	$message->subject('New verified customer - pending organizational approval');
        	$message->body($verifyTemplate->output());

        	app_log("Sending Admin Confirm new customer reminder",'debug',__FILE__,__LINE__);
        	$transport = \Email\Transport::Create(array('provider' => $GLOBALS['_config']->email->provider));
        	$transport->hostname($GLOBALS['_config']->email->hostname);
        	$transport->token($GLOBALS['_config']->email->token);
        	$transport->deliver($message);
        	if ($transport->error) {
        		$page->addError("Error sending email, please contact us at service@spectrosinstruments.com");
        		app_log("Error Sending Admin Confirm new customer reminder: ".$transport->error,'error',__FILE__,__LINE__);
        		return;
        	}
            $page->isVerifedAccount = true;
        }
    }
