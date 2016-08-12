<?PHP
	# Handle Actions
	if (($_REQUEST['email_address']) and (valid_email($_REQUEST['email_address'])))
	{
		# Get User Info From Database
		$_contact = new RegisterContact();
		list($contact) = $_contact->find(array("type" => 'email', "value" => $_REQUEST['email_address']));
		if ($_contact->error)
		{
			app_log("Error finding contact: ".$_contact->error,'error',__FILE__,__LINE__);
			$GLOBALS['_page']->error = "Error finding contact info, please try again later";
			return null;
		}

		if ($contact->person_id)
		{
			$_customer = new RegisterCustomer();
			$customer = $_customer->details($contact->person_id);

			# Make Sure the Customer Has a Real Login
			if (! isset($customer->code) or ! strlen($customer->code))
			{
				$GLOBALS['_page']->error .= "Sorry, you have not been given access to this site.";
				return;
			}

			# Generate a Password Recovery Token
			$_token = new RegisterPasswordToken();
			$token = $_token->add($contact->person_id);
			if ($_token->error)
			{
				app_log("Error generating password token: ".$_token->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = "Error generating password token";
				return;
			}

			# Load SMTP Class
			require_module("email");

			###############################################
			### Password Found, Generate Recovery Email	###
			###############################################
			# Generate Email Body
			$message = <<< EOT
<html>
<head>
	<title> Password Request </title>
	<style>
		.copy {
			font-family: arial, helvetica, sans-serif;
			font-weight: normal; font-size: 12px;
		}
	</style>
</head>
<body>
<p class="copy">A password recovery request was submitted for your account.  If you did not submit this request, you can safely ignore this message.</p>
<p class="copy">Your Login is <span class="highlight">$customer->login</span></p>
<p class="copy">Click <a href="http://{$_SERVER['SERVER_NAME']}/_register/login?token=$token&target=_register:account">here</a> to login so you can reset your password.</p>
<p class="copy">This is a one time login token which will be deleted upon use.  It will expire after 24 hours if not used.</p>
<p class="copy">Thank You</p>
<p class="copy">Spectros Instruments</p>
</body>
</html>
EOT;

			$mail = array(
				"to"		=> $_REQUEST['email_address'],
				"from"		=> '"Spectros Instruments" <no_reply@spectrosinstruments.com>',
				"subject"	=> "Password Recovery",
				"body"	=> $message
			);

			###################################################
			### Send Confirmation Email						###
			###################################################
			app_log("Sending password recovery email to ".$contact->value,'notice',__FILE__,__LINE__);
			$_email = new EmailMessage();
			$_email->send($mail);
			if ($_email->error)
			{
				app_log("Error sending notification: ".$_email->error,'error',__FILE__,__LINE__);
                $GLOBALS['_page']->error = "Sorry, there was an error sending your information.  Please try again later";
				return;
			}

			# Let them know an email was sent
			$GLOBALS['_page']->success = "<B>Thank You.</B><BR> An email has been sent containing your new password information.<BR>\n";

			$GLOBALS['_page']->success .= "Close window and return to login page.\n";

		}
		else
		{
			$GLOBALS['_page']->error = "Sorry, your email address was not found in our database.\n";
			$GLOBALS['_page']->error .= "Please use our Contact Us form to recover your account information.\n";
		}
	}
	elseif($_REQUEST['email_address']) $GLOBALS['_page']->error .= "Sorry, but the email address you gave was not valid!";

	function valid_email($email)
	{
		if (preg_match("/^[\w\-\_\.\+]+@[\w\-\_\.]+$/",$email)) return 1;
		else return 0;
	}
?>
