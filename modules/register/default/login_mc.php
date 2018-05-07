<?PHP
	###########################################################
	### login_mc.php										###
	### This program is the main content file for			###
	### login.php.  This program allows a customer to		###
	### identify themselves via login name and password.	###
	### It will flag the session and order record with		###
	### the customer id if login successful.				###
	### A. Caravello 8/25/2002								###
	###########################################################

	if (isset($_POST['login_target']))
		$target = $_POST['login_target'];
	elseif(isset($_GET['target']))
		# Translate target
		$target = preg_replace('/\:/','/',urldecode($_GET["target"]));
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
		# Consume Token
		$_token = new RegisterPasswordToken();
		$new_id = $_token->consume($_REQUEST['token']);
		if ($_token->error) {
			app_log("Error in password recovery: ".$_recovery->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Error in password recovery.  Admins have been notified.  Please try again later.";
		}
		elseif ($new_id > 0) {
			$customer = new RegisterCustomer($new_id);
			$GLOBALS['_SESSION_']->customer = $customer;

			$GLOBALS['_SESSION_']->update(
				$GLOBALS['_SESSION_']->id,
				array(
					"user_id" => $customer->id,
					"timezone"	=> $customer->timezone
				)
			);

			app_log("Customer ".$customer->id." logged in by token",'notice',__FILE__,__LINE__);
			app_log("login_target = $target",'notice',__FILE__,__LINE__);
			app_log("Redirecting to ".PATH.$target,'notice',__FILE__,__LINE__);
			header("location: ".PATH.$target);
			exit;
		}
		else {
			$GLOBALS['_page']->error = "Sorry, your recovery token was not recognized or has expired";
		}
	}
	elseif (isset($_REQUEST['login'])) {
		$customer = new \Register\Customer();
		if (! $customer->authenticate($_REQUEST['login'],$_REQUEST['password'])) {
			$GLOBALS['_page']->error = "Authentication failed";
		}
		elseif ($customer->error) {
			app_log("Error in authentication: ".$customer->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error .= "Application Error";
		}
		elseif ($customer->message) {
			$GLOBALS['_page']->error .= $customer->message;
		}
		else {
			$customer->get($_REQUEST['login']);
			$GLOBALS['_SESSION_']->assign($customer->id);
			$GLOBALS['_SESSION_']->touch();

			app_log("Customer ".$customer->id." logged in",'notice',__FILE__,__LINE__);
			app_log("login_target = $target",'notice',__FILE__,__LINE__);
			app_log("Redirecting to ".PATH.$target,'notice',__FILE__,__LINE__);
			header("location: ".PATH.$target);
			exit;
		}
	}
	else {
		app_log("No authentication information sent",'debug',__FILE__,__LINE__);
	}
?>
