<?PHP
	###################################################
	### register_mc.php								###
	### This program collects registration info		###
	### for the user.								###
	### A. Caravello 11/12/2002						###
	###################################################

	$page = new \Site\Page(array("module" => 'register',"view" => 'account'));

	# Security - Only Register Module Operators or Managers can see other customers
	if (! $GLOBALS['_SESSION_']->customer->has_role('register manager')) {
		$page->error("Permission Denied");
		return;
	}
	if (isset($_REQUEST['customer_id']) && preg_match('/^\d+$/',$_REQUEST['customer_id'])) $customer_id = $_REQUEST['customer_id'];
	elseif (preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
		$customer = new \Register\Customer();
		$customer->get($code);
		if ($customer->id)
			$customer_id = $customer->id;
		else
			$page->error = "Customer not found";
	}
	else $customer_id = $GLOBALS['_SESSION_']->customer->id;

	app_log($GLOBALS['_SESSION_']->customer->login." accessing account of customer ".$customer_id,'notice',__FILE__,__LINE__);

	#######################################
	### Handle Actions					###
	#######################################
	if (isset($_REQUEST['method']) && $_REQUEST['method'] == "Apply") {
		app_log("Account form submitted",'debug',__FILE__,__LINE__);
		$parameters = array();
		if (isset($_REQUEST["first_name"])) 	$parameters['first_name']	= $_REQUEST["first_name"];
		if (isset($_REQUEST["last_name"]))		$parameters['last_name']	= $_REQUEST["last_name"];
		if (isset($_REQUEST["timezone"]))		$parameters['timezone']		= $_REQUEST["timezone"];
		if (isset($_REQUEST["roles"]))			$parameters['roles']		= $_REQUEST["role"];
		if (isset($_REQUEST["status"]))			$parameters['status']		= $_REQUEST["status"];

		if (isset($_REQUEST['organization_id'])) {
			$parameters["organization_id"] = $_REQUEST["organization_id"];
		}
		if (isset($_REQUEST["password"]) and ($_REQUEST["password"])) {
			if ($_REQUEST["password"] != $_REQUEST["password_2"]) {
				$page->error .= "Passwords do not match";
				goto load;
			}
			else
				$parameters["password"] = $_REQUEST["password"];
		}

		if ($customer_id) {
			app_log("Updating customer ".$customer_id,'debug',__FILE__,__LINE__);
			$customer = new \Register\Customer($customer_id);

			$customer->update($parameters);
			if ($customer->error) {
				app_log("Error updating customer: ".$customer->error,'error',__FILE__,__LINE__);
				$page->error = "Error updating customer information.  Our admins have been notified.  Please try again later";
				goto load;
			}
		}
		else {
			app_log("New customer registration",'debug',__FILE__,__LINE__);
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
	
			if ($customer->error) {
				$page->error .= $customer->error;
				goto load;
			}

			if ($customer->id) {
				$GLOBALS['_SESSION_']->update(array("user_id" => $customer->{id}));
				if ($GLOBALS['_SESSION_']->error) {
					$page->error .= "Error updating session: ".$GLOBALS['_SESSION_']->error;
					goto load;
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
				$page->error = "Sorry, we were unable to complete your registration";
				goto load;
			}

			# Redirect to Address Page If Order Started
			if (isset($target)) $next_page = $target;
			elseif (isset($order_id)) $next_page = "/_cart/address";
			else $next_page = "/_register/thank_you";
			header("Location: $next_page");
		}
		
		# Process Contact Entries
		app_log("Processing contact entries",'debug',__FILE__,__LINE__);
		while (list($contact_id) = each($_REQUEST['type'])) {
			if (! $_REQUEST['type'][$contact_id]) continue;

			if ($contact_id > 0) {
				app_log("Updating contact record",'debug',__FILE__,__LINE__);
				$contact = new \Register\Contact($contact_id);

				# Update Existing Contact Record
				$contact->update(
					array(
						"type"			=> $_REQUEST['type'][$contact_id],
						"description"	=> $_REQUEST['description'][$contact_id],
						"value"			=> $_REQUEST['value'][$contact_id],
						"notes"			=> $_REQUEST['notes'][$contact_id]
					)
				);
				if ($contact->error) {
					$page->error .= "Error updating contact: ".$customer->error;
					goto load;
				}
			}
			else {
				app_log("Adding contact record",'debug',__FILE__,__LINE__);
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
				if ($customer->error) {
					$page->error .= "Error adding contact: ".$customer->error;
					goto load;
				}
			}
		}

		# Get List Of Possible Roles
		app_log("Checking roles",'notice',__FILE__,__LINE__);
		$rolelist = new \Register\RoleList();
		$available_roles = $rolelist->find();
		app_log("Found ".$rolelist->count." roles",'trace',__FILE__,__LINE__);

		# Loop through all roles and apply
		# changes if necessary
		foreach ($available_roles as $role) {
			app_log("Checking role ".$role->name."[".$role->id."]",'trace',__FILE__,__LINE__);
			if (isset($_REQUEST['role'][$role->id]) && $_REQUEST['role'][$role->id]) {
				app_log("Role is selected",'trace',__FILE__,__LINE__);
				if (! $customer->has_role($role->name)) {
					app_log("Adding role ".$role->name." for ".$customer->login,'debug',__FILE__,__LINE__);
					$customer->add_role($role->id);
				}
			}
			else {
				app_log("Role is not selected",'trace',__FILE__,__LINE__);
				if ($customer->has_role($role->name)){
					app_log("Role ".$role->name." being revoked from ".$customer->login,'debug',__FILE__,__LINE__);
					$customer->drop_role($role->id);
				}
			}
		}
		
		$page->success = 'Your changes have been saved';
	}

	load:
	if ($customer_id) {
		$customer = new \Register\Customer($customer_id);
		$contacts = $customer->contacts();
	}
	$rolelist = new \Register\RoleList();
	$all_roles = $rolelist->find();
	$_department = new \Register\Department();
	$departments = $_department->find();
	app_log("Loading Organizations",'trace',__FILE__,__LINE__);
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();
	$_contact = new \Register\Contact();
	$contact_types = $_contact->types;
	
	if (! isset($target)) $target = '';
?>
