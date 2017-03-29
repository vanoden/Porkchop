<?PHP
	###################################################
	### register_mc.php								###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################

	# Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
		if (isset($_REQUEST['customer_id']) && preg_match('/^\d+$/',$_REQUEST['customer_id'])) $customer_id = $_REQUEST['customer_id'];
		elseif (preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$customer = new \Register\Customer();
			$customer->get($code);
			if ($customer->id)
				$customer_id = $customer->id;
			else
				$GLOBALS['_page']->error = "Customer not found";
		}
		else $customer_id = $GLOBALS['_SESSION_']->customer->id;
	}
	elseif (isset($GLOBALS['_SESSION_']->customer->id)) $customer_id = $GLOBALS['_SESSION_']->customer->id;
	else {
		header("location: /_register/login?target=_register/account");
		exit;
	}
	app_log($GLOBALS['_SESSION_']->customer->login." accessing account of customer ".$customer_id,'notice',__FILE__,__LINE__);

	#######################################
	### Handle Actions					###
	#######################################
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply") {
		$parameters = array();
		if (isset($_REQUEST["first_name"])) 	$parameters['first_name']	 = $_REQUEST["first_name"];
		if (isset($_REQUEST["last_name"]))		$parameters['last_name']	 = $_REQUEST["last_name"];
		if (isset($_REQUEST["timezone"]))		$parameters['timezone']		 = $_REQUEST["timezone"];
		if (isset($_REQUEST["roles"]))			$parameters['roles']		 = $_REQUEST["role"];

		if (role("register manager")) $parameters["organization_id"] = $_REQUEST["organization_id"];
		if (isset($_REQUEST["password"]) and ($_REQUEST["password"])) {
			if ($_REQUEST["password"] != $_REQUEST["password_2"])
				$GLOBALS['_page']->error .= "Passwords do not match";
			else
				$parameters["password"] = $_REQUEST["password"];
		}

		if ($customer_id) {
			$customer = new \Register\Customer($customer_id);

			$customer->update($parameters);
			if ($customer->error)
			{
				app_log("Error updating customer: ".$customer->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = "Error updating customer information.  Our admins have been notified.  Please try again later";
			}
		}
		else {
			# Default Login to Email Address
			if (! $_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['email_address'];

			# Generate Validation Key
			$validation_key = md5(microtime());

			$parameters["login"] = $_REQUEST['login'];
			
			###########################################
			### Add User To Database				###
			###########################################
			# Add Customer Record to Database
			$customer = new \Register\Customer();
			$customer->add($parameters);
	
			if ($customer->error)
			{
				$GLOBALS['_page']->error .= $customer->error;
				return;
			}

			if ($customer->id) {
				$GLOBALS['_SESSION_']->update(array("user_id" => $customer->{id}));
				if ($GLOBALS['_SESSION_']->error) {
					$GLOBALS['_page']->error .= "Error updating session: ".$GLOBALS['_SESSION_']->error;
				}
			}

			# Registration Confirmation
			$_contact = new \Register\Contact();
			$_contact->notify(array(
					"from"		=> $GLOBALS['_config']->register->confirmation->from,
					"subject"	=> $GLOBALS['_config']->register->confirmation->subject,
					"message"	=> "Thank you for registering",
				)
			);
			if ($_contact->error) {
				app_log("Error sending registration confirmation: ".$_contact->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = "Sorry, we were unable to complete your registration";
			}

			# Redirect to Address Page If Order Started
			if (isset($target)) $next_page = $target;
			elseif (isset($order_id)) $next_page = "/_cart/address";
			else $next_page = "/_register/thank_you";
			header("Location: $next_page");
		}
		while (list($contact_id) = each($_REQUEST['type'])) {
			if (! $_REQUEST['type'][$contact_id]) continue;

			if ($contact_id > 0) {
				# Update Existing Contact Record
				$customer->updateContact(
					$contact_id,
					array(
						"type"			=> $_REQUEST['type'][$contact_id],
						"description"	=> $_REQUEST['description'][$contact_id],
						"value"			=> $_REQUEST['value'][$contact_id],
						"notes"			=> $_REQUEST['notes'][$contact_id]
					)
				);
				if ($customer->error)
					$GLOBALS['_page']->error .= "Error updating contact: ".$customer->error;
			}
			else {
				app_log("Add Contact Request:\n".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);
				# Create Contact Record
				$customer->addContact(
					array(
						"person_id"		=> $customer_id,
						"type"			=> $_REQUEST['type'][0],
						"description"	=> $_REQUEST['description'][0],
						"value"			=> $_REQUEST['value'][0],
						"notes"			=> $_REQUEST['notes'][0]
					)
				);
				if ($customer->error)
					$GLOBALS['_page']->error .= "Error adding contact: ".$customer->error;
			}
		}
		
		# Get List Of Possible Roles
		$rolelist = new \Register\RoleList();
		$available_roles = $rolelist->find();

		# Get Roles to which Customer Belongs
		$current_roles = $customer->roles($customer_id,true);
		app_log(print_r($current_roles,true),'debug');

		# Loop through all roles and apply
		# changes if necessary
		foreach ($available_roles as $role) {
			//app_log("Checking role ".$role->id,'debug');
			if (isset($_REQUEST['role'][$role->id]) && $_REQUEST['role'][$role->id]) {
				if (! in_array($role->id,$current_roles)) {
					app_log("Adding role ".$role->id,'debug',__FILE__,__LINE__);
					$customer->add_role($role->id);
				}
			}
			else {
				if (in_array($role->id,$current_roles)){
					app_log("Role ".$role->id." being revoked",'debug',__FILE__,__LINE__);
					$customer->drop_role($role->id);
				}
			}
		}
	}

	if ($customer_id) {
		$customer = new \Register\Customer($customer_id);
		$customer->details();
		$contacts = $customer->findContacts(
			array("person_id" => $customer_id)
		);
	}
	$rolelist = new \Register\RoleList();
	$all_roles = $rolelist->find();
	$_department = new \Register\Department();
	$departments = $_department->find();
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();
	$_contact = new \Register\Contact();
	$contact_types = $_contact->types;
	
	if (! isset($target)) $target = '';
?>
