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
	session_start();	
	if (!isset($_SESSION['failedAttemptCount'])) $_SESSION['failedAttemptCount'] = 0;
	$page = new \Site\Page();
    $target = "";
	if (isset($_REQUEST['return']) && $_REQUEST['return'] == 'true') {
		# This Is How They SHOULD Come In from Redirect
		if (isset($_REQUEST['module']) && isset($_REQUEST['view'])) {
			$target = "/_".$_REQUEST['module']."/".$_REQUEST['view'];
		} else {
			$target = $GLOBALS['_REQUEST_']->refererURI();
			app_log("Return to ".$GLOBALS['_REQUEST_']->refererURI()." after login");
		}
	} elseif (isset($_POST['login_target']))
		# This is how the SHOULD come in from FORM submit
		$target = $_POST['login_target'];
	elseif(isset($_GET['target']))
		# Translate target
		$target = urldecode($_GET["target"]);
	elseif($GLOBALS['_config']->register->auth_target)
		$target = $GLOBALS['_config']->register->auth_target;
	if (! preg_match('/^\//',$target))
		$target = '/'.$target;

	if (($GLOBALS['_SESSION_']->customer->id) and ($target != '/'))	{
		app_log("Redirecting ".$GLOBALS['_SESSION_']->customer->code." to ".PATH.$target,'notice',__FILE__,__LINE__);
		header("location: ".PATH.$target);
		exit;
	}
	
	# Handle Input
	if (isset($_REQUEST['token']) and (preg_match('/^[a-f0-9]{64}$/',$_REQUEST['token']))) {
		app_log('Auth By Token','debug',__FILE__,__LINE__);
		# Consume Token
		$token = new \Register\PasswordToken();
		$customer_id = $token->consume($_REQUEST['token']);
		if ($token->error) {
			app_log("Error in password recovery: ".$token->error,'error',__FILE__,__LINE__);
			$page->addError("Error in password recovery.  Admins have been notified.  Please try again later.");
		} elseif ($customer_id > 0) {
			$customer = new \Register\Customer($customer_id);
			if ($customer->error) {
				app_log("Error getting customer: ".$customer->error,'error',__FILE__,__LINE__);
				$page->addError("Token error");
			} elseif(! $customer->id) {
				app_log("Customer not found!",'notice',__FILE__,__LINE__);
				$page->addError("Token error");
			} else {
				$GLOBALS['_SESSION_']->assign($customer->id);

				app_log("Customer ".$customer->id." logged in by token",'notice',__FILE__,__LINE__);
				app_log("Redirecting to '/_register/reset_password'",'notice',__FILE__,__LINE__);
				header("location: /_register/reset_password");
				exit;
			}
		} else {
			$page->addError("Sorry, your recovery token was not recognized or has expired");
		}
	} elseif (isset($_REQUEST['login'])) {
		app_log("Auth by login/password",'debug',__FILE__,__LINE__);
		$customer = new \Register\Customer();		
		if (! $customer->authenticate($_REQUEST['login'],$_REQUEST['password'])) {
			$counter = new \Site\Counter("auth_failed");
			$counter->increment();
		    $customer->get($_REQUEST['login']);
		    if ((isset($_SESSION['isRemovedAccount']) && $_SESSION['isRemovedAccount'] == 1) || $_SESSION['failedAttemptCount'] > 2 || $customer->status == 'EXPIRED' || $customer->status == 'DELETED') {
		    
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
			        app_log("ReCAPTCHA presented and SOLVED for " . $customer->status . " Customer (must be a human attempting)" , 'notice' , __FILE__ , __LINE__);
			        $_SESSION['failedAttemptCount'] = 0;
			        $customer->update(array('status' => 'ACTIVE'));
		        } else {
			        $page->addError("Sorry, CAPTCHA Invalid.  Please Try Again");
			        app_log("ReCAPTCHA presented and FAILED for " . $customer->status . " Customer" , 'notice' , __FILE__ , __LINE__);
		        }
		    
		        // if a old or deleted account login, then we'll force over to the login page with a captcha
    		    $_SESSION['isRemovedAccount'] = 1;
			    app_log("Customer ".$customer->id. " " . $customer->status . " login ATTEMPTED",'notice',__FILE__,__LINE__);
			    app_log("login_target = $target",'debug',__FILE__,__LINE__);	
			    header("location: ".PATH.'/_register/login');	
		    } else {
    			app_log("Customer ".$customer->id." login failed",'notice',__FILE__,__LINE__);
			    app_log("login_target = $target",'debug',__FILE__,__LINE__);
		    }
		    
		    // track failed attempts at login for 
		    $_SESSION['failedAttemptCount'] = $_SESSION['failedAttemptCount'] + 1;
			$counter = new \Site\Counter("auth_blocked");
			$counter->increment();
		    if ($_SESSION['failedAttemptCount'] > 3) $customer->update(array('status' => 'EXPIRED'));
			$page->addError("Authentication failed");
		} elseif ($customer->error) {
			app_log("Error in authentication: ".$customer->error,'error',__FILE__,__LINE__);
			$page->addError("Application Error");
		} elseif ($customer->message) {
			$page->addError($customer->message);
		} else {
			$customer->get($_REQUEST['login']);
			$GLOBALS['_SESSION_']->assign($customer->id);
			$GLOBALS['_SESSION_']->touch();

			app_log("Customer ".$customer->id." logged in",'debug',__FILE__,__LINE__);
			app_log("login_target = $target",'debug',__FILE__,__LINE__);
			app_log("Redirecting to ".PATH.$target,'debug',__FILE__,__LINE__);
			header("location: ".PATH.$target);
			exit;
		}
	} else {
		app_log("No authentication information sent",'debug',__FILE__,__LINE__);
	}
