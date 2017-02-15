<?PHP
	###################################################
	### register_mc.php								###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################

	#error_log(print_r($_REQUEST,true));

	###########################
	### Handle Actions		###
	###########################
	if ($_REQUEST['method'] == "Apply") {
		# Initialize Admin Object
		$_customer = new \Register\Admin();
		if ($_customer->password_strength($_REQUEST['password']) < $_GLOBALS['_config']->register->minimum_password_strength) {
			$GLOBALS["_page"]->error = "Password not strong enough";
		}
		elseif ($_REQUEST["password"] != $_REQUEST["password_2"]) {
			$GLOBALS['_page']->error .= "Passwords do not match";
		}
		else {
			# Default Login to Email Address
			if (! $_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['email_address'];

			# Generate Validation Key
			$validation_key = md5(microtime());

			# Make Sure Login is unique
			$already_exists = $_customer->get($_REQUEST['login']);
			if ($already_exists->id) {
				$GLOBALS['_page']->error = "Sorry, login already taken";
				$_REQUEST['login'] = '';
			}
			else {
				###########################################
				### Add User To Database				###
				###########################################
				# Add Customer Record to Database
				$customer = $_customer->add(
					array(
						"login"				=> $_REQUEST['login'],
						"password"			=> $_REQUEST['password'],
						"first_name"		=> $_REQUEST['first_name'],
						"last_name"			=> $_REQUEST['last_name'],
						"validation_key"	=> $validation_key,
					)
				);
				if ($_customer->error) {
					app_log("Error adding customer: ".$_customer->error,'error',__FILE__,__LINE__);
					$GLOBALS['_page']->error .= "Sorry, there was an error adding your account.  Our admins have been notified.  Please try again later";
				}
				else {
					# Login New User by updating session
					$GLOBALS['_SESSION_']->update(array("user_id" => $customer->{id}));
					if ($GLOBALS['_SESSION_']->error) {
						$GLOBALS['_page']->error .= "Error updating session: ".$GLOBALS['_SESSION_']->error;
					}

					# Create Contact Record
					if ($_REQUEST['work_email']) {
						$_customer->addContact(
							array(
								"person_id"		=> $customer_id,
								"type"			=> "email",
								"description"	=> "Work Email",
								"value"			=> $_REQUEST['work_email']
							)
						);
						if ($_customer->error)
							app_log("Error adding Work Email '".$_REQUEST['work_email']."': ".$_customer->error,'error',__FILE__,__LINE__);
					}
					if ($_REQUEST['home_email']) {
						# Create Contact Record
						$_customer->addContact(
							array(
								"person_id"		=> $customer_id,
								"type"			=> "email",
								"description"	=> "Home Email",
								"value"			=> $_REQUEST['home_email']
							)
						);
						if ($_customer->error)
							app_log("Error adding Home Email '".$_REQUEST['home_email']."': ".$_customer->error,'error',__FILE__,__LINE__);
					}
	
					# Generate Email Confirmation
					$message = "<html>\n";
					$message .= "<span class=\"email_header\">".$GLOBALS['_config']->register->confirmation->header."</span><br><br>\n";
					$message .= "<span class=\"email_label\">Your login is: </span><span class=\"email_value\">".$_REQUEST['login']."</span><br>\n";
		
					$message .= "<span class=\"email_body\">".$GLOBALS['_config']->register->confirmation->footer."</span><br>\n";
					$message .= "</html>\n";
		
					###################################################
					### Send Confirmation Email						###
					###################################################
					$parameters = array();
					if ($_REQUEST['work_email']) $parameters['to'] = $_REQUEST['work_email'];
					else $parameters['to'] = $_REQUEST['home_email'];
					$parameters['from'] = $GLOBALS['_config']->register->confirmation->from;
					$parameters['body'] = $message;
					$parameters['subject'] = $GLOBALS['_config']->register->confirmation->subject;
		
					$_email = new \Email\Message();
					$_email->send($parameters);
					if ($_email->error) app_log($_email->error,'error',__FILE__,__LINE__);
					$GLOBALS['_page']->error = "Failed to send confirmation email";
		
					# Redirect to Address Page If Order Started
					if ($target) $next_page = $target;
					elseif ($order_id) $next_page = "/_cart/address";
					else $next_page = "/_register/thank_you";
					header("Location: $next_page");
				}
			}
		}
	}
?>
