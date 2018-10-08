<?php
    ###############################################
    ### Handle API Request for Customer Info 	###
    ### and Management							###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$schema = new \Register\Schema();

	$_package = array(
		"name"		=> "register",
		"version"	=> "0.2.0",
		"release"	=> "2018-05-09",
		"schema"	=> $schema->version(),
	);

	# Call Requested Event
	//error_log($_REQUEST['action']." Request received from ".$_REQUEST['hub_code']);
	if (isset($_REQUEST["method"])) {
		header('Access-Control-Allow-Origin: *');  
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name($_package);
		exit;
	}
    # Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('register manager')) {
        header("location: /_register/login");
        exit;
    }

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping($_package) {
		$response = new stdClass();
		$response->message = "PING RESPONSE";
		$response->schema_version = $_package["schema"];
		$response->package_version = $_package["version"];
		$response->release_date = $_package["release"];
		$response->success = 1;
		print formatOutput($response);
	}

	###################################################
	### Get Details regarding Current Customer		###
	###################################################
	function me() {
		# Default StyleSheet
		if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'register.customer.xsl';

		if ($GLOBALS['_SESSION_']->customer->has_role('administrator')) {
			$GLOBALS['_SESSION_']->customer->admin = 1;
		}
		$response = new stdClass();
		$response->customer = $GLOBALS['_SESSION_']->customer;
		$response->success = 1;

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Authenticate Session						###
	###################################################
	function authenticateSession() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.customer.xsl';

		# Initiate Product Object
		$customer = new \Register\Customer();

		$result = $customer->authenticate($_REQUEST["login"],$_REQUEST["password"]);
		if ($customer->error) error($customer->error);

		if ($result > 0) {
			app_log("Assigning session ".$GLOBALS['_SESSION']->id." to customer ".$customer->id,'debug',__FILE__,__LINE__);
			$GLOBALS['_SESSION_']->assign($customer->id);
		}
		else {
			app_log("Authentication failed",'notice',__FILE__,__LINE__);
		}
		
		$response = new stdClass();
		$response->success = $result;
		if (! $result) $response->message = "Invalid login password combination";

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Get Details regarding Specified Customer	###
	###################################################
	function getCustomer() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.customer.xsl';

		# Initiate Product Object
		$customer = new \Register\Customer();

		if ($GLOBALS['_SESSION_']->customer->has_role('register reporter')) {
			# Can Get Anyone
		}
		elseif ($GLOBALS['_SESSION_']->customer->id = $customer->id) {
			# Can Get Yourself
		}
		else {
			error('Permission denied');
		}

		if ($_REQUEST["login"] and (! $_REQUEST{"code"})) $_REQUEST['code'] = $_REQUEST['login'];
		$customer->get($_REQUEST["code"]);

		# Error Handling
		if ($customer->error) error($customer->error);
		else{
			$response = new stdClass();
			$response->customer = $customer;
			$response->success = 1;
		}

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Update Specified Customer					###
	###################################################
	function updateCustomer() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.customer.xsl';

		# Initiate Product Object
		$customer = new \Register\Customer();

		# Find Customer
		$customer->get($_REQUEST['code']);
		if ($customer->error) app_error("Error getting customer: ".$customer->error,__FILE__,__LINE__);
		if (! $customer->id) error("Customer not found");

		if ($GLOBALS['_SESSION_']->customer->has_role('register admin')) {
			# Can Update Anyone
		}
		elseif ($GLOBALS['_SESSION_']->customer->id = $customer->id) {
			# Can Update Yourself
		}
		else {
			error('Permission denied');
		}

		if ($_REQUEST['organization']) {
			$_organization = new \Register\Organization();
			$organization = $_organization->get($_REQUEST['organization']);
			if ($_organization->error) app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$parameters['organization_id'] = $organization->id;
		}
		
		if ($_REQUEST['first_name']) $parameters['first_name'] = $_REQUEST['first_name'];
		if ($_REQUEST['last_name']) $parameters['last_name'] = $_REQUEST['last_name'];
		if ($_REQUEST['password']) $parameters['password'] = $_REQUEST['password'];

		# Update Customer
		$customer = $_customer->update(
			$customer->id,
			$parameters
		);

		# Error Handling
		if ($_customer->error) app_error("Error updating customer: ".$_customer->error,__FILE__,__LINE__);
		$response = new stdClass();
		$response->customer = $customer;
		$response->success = 1;

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Find Customers								###
	###################################################
	function findCustomers() {
		# Default StyleSheet
		if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'register.customers.xsl';

		# Build Query Parameters
		$parameters = array();
		if ($GLOBALS['_SESSION_']->customer->has_role('register reporter')) {
			if ($_REQUEST["organization_code"]) {
				app_log("Getting organization '".$_REQUEST['organization_code']."'",'debug',__FILE__,__LINE__);
				$organization = new \Register\Organization();
				$organization->get($_REQUEST["organization_code"]);
				if ($organization->error) app_error("Error finding organization: ".$organization->error,'error',__FILE__,__LINE__);
				if (! $organization->id) error("Could not find organization '".$_REQUEST["organization_code"]."'");
				$parameters['organization_id'] = $organization->id;
			}
		}
		elseif (isset($GLOBALS['_SESSION_']->customer->organization->id)) {
			$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
		}
		else {
			error('Permission denied');
		}
		if ($_REQUEST["code"]) $parameters["code"] = $_REQUEST["code"];
		elseif ($_REQUEST["login"]) $parameters["code"] = $_REQUEST["login"];
		if ($_REQUEST["first_name"]) $parameters["first_name"] = $_REQUEST["first_name"];
		if ($_REQUEST["last_name"]) $parameters["last_name"] = $_REQUEST["last_name"];

		# Get List of Matching Customers
		$customerlist = new \Register\CustomerList();
		$customers = $customerlist->find($parameters);

		# Error Handling
		if ($customerlist->error) error($customerlist->error);

		$response = new stdClass();
		$response->success = 1;
		$response->customer = $customers;

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Find Roles									###
	###################################################
	function findRoles() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('register reporter') && ! $GLOBALS['_SESSION_']->customer->has_role('register admin')) error('Permission denied');

		$roleList = new \Register\RoleList();
		$roles = $roleList->find();
		
		$response = new stdClass();
		$response->success = 1;
		$response->role = $roles;
		
		print formatOutput($response);
	}
	###################################################
	### Find Role Members							###
	###################################################
	function findRoleMembers() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.rolemembers.xsl';

		if (! $GLOBALS['_SESSION_']->customer->has_role('register reporter') && ! $GLOBALS['_SESSION_']->customer->has_role('register admin')) error('Permission denied');

		# Initiate Role Object
		$role = new \Register\Role();
		$role->get($_REQUEST['code']);

		$response->request->parameter = $parameters;

		# Get List of Matching Admins
		$admins = $role->members();

		# Error Handling
		if ($role->error) error($role->error);

		$response = new stdClass();
		$response->success = 1;
		$response->admin = $admins;

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Add a User Role								###
	###################################################
	function addRole() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('register manager')) error("Permission denied");

		$role = new \Register\Role();
		$result = $role->add(
			array(
				'name'	=> $_REQUEST['name'],
				'description'	=> $_REQUEST['description']
			)
		);
		if ($role->error) error($role->error);

		$response = new stdClass();
		$response->success = 1;
		$response->role = $result;

		print formatOutput($response);
	}
	###################################################
	### Add a User to a Role						###
	###################################################
	function addRoleMember() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('register manager')) error("Permission denied");

		$role = new Role();
		$role->get($_REQUEST['name']);
		if ($role->error) app_error("Error getting role: ".$role->error,'error',__FILE__,__LINE__);
		if (! $role->id) error("Role not found");
		
		$person = new \Register\Person();
		$person->get($_REQUEST['login']);
		if ($person->error) app_error("Error getting person: ".$person->error,'error',__FILE__,__LINE__);
		if (! $person->id) error("Person not found");

		$result = $role->addMember($person->id);
		if ($role->error) error($role->error);

		$response = new stdClass();
		$response->success = 1;

		print formatOutput($response);
	}
	###################################################
	### Create Customer Image						###
	###################################################
	function addImage() {
		# Authenticated Customer Required
		#confirm_customer();

		# Initiate Response
		$response = new stdClass();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'gallery.image.xsl';

		# Initiate Image Object
		$_image = new Image();

		# Add Event
		$_image->add(
			array(
				"name"			=> $_FILES["upload"]["name"],
				"description"	=> $_REQUEST["description"],
				"tmp_name"		=> $_FILES["upload"]["tmp_name"],
				"category"		=> $_REQUEST["category"]
			)
		);

		# Error Handling
		if ($_image->error) error($_image->error);
		else{
			$response->image = $_image->details();
			$response->success = 1;
		}

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Add a New Customer via Registration			###
	###################################################
	function addCustomer() {
		# Initiate Response
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.user.xsl';

		# Initiate Image Object
		$user = new \Register\Customer();
		$user->get($_REQUEST['login']);
		if ($user->id) {
			error("Duplicate Login");
		}

		$organization_id = 0;
		if ($_REQUEST['organization_id']) {
			$organization = new \Register\Organization($_REQUEST['organization_id']);
            if ($organization->error) app_error("Error finding organization: ",'error',__FILE__,__LINE__);
            if (! $organization->id) error("Could not find organization by id");
            $organization_id = $organization->id;
		}
		elseif ($_REQUEST['organization']) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
			if ($organization->error) app_error("Error finding organization: ",'error',__FILE__,__LINE__);
			if (! $organization->id) error("Could not find organization");
			$organization_id = $organization->id;
		}

		if (! $_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['code'];

		# Add Event
		$user->add(
			array(
				first_name		=> $_REQUEST['first_name'],
				last_name		=> $_REQUEST['last_name'],
				login			=> $_REQUEST['login'],
				password		=> $_REQUEST['password'],
				organization_id	=> $organization_id,
				custom_1		=> $_REQUEST['custom_1'],
				custom_2		=> $_REQUEST['custom_2'],
			)
		);

		# Error Handling
		if ($user->error) error($user->error);
		$response = new stdClass();
		$response->customer = $user;
		$response->success = 1;

		# Send Response
		print formatOutput($response);
	}
	function findContacts() {
		$_contact = new \Register\Contact();

		if (isset($_REQUEST['person']))
		{
			$_customer = new \Register\Customer();
			$customer = $_customer->get($_REQUEST['person']);
			if ($_customer->error) error($_customer->error);
			$customer_id = $customer->id;
			if (! $customer_id) app_error("Customer not found");
		}

		$parameters = array();
		if (isset($customer_id) and $customer_id) $parameters['person_id'] = $customer_id;
		if (isset($_REQUEST['type']) and $_REQUEST['type']) $parameters['type'] = $_REQUEST['type'];
		if (isset($_REQUEST['value']) and $_REQUEST['value']) $parameters['value'] = $_REQUEST['value'];
		
		$contact = $_contact->find($parameters);
		if ($_contact->error) error($_contact->error);
		$response = new stdClass();
		$response->contact = $contact;
		$response->success = 1;
		

		# Send Response
		print formatOutput($response,array());
	}
	###################################################
	### Verify Users Email Address					###
	###################################################
	function verifyEmail() {
		# Initiate Response
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.verify.xsl';

		# Initiate Image Object
		$_user = new Customer();

		# Add Event
		$_user->verify_email($_REQUEST['login'],$_REQUEST['validation_key']);

		# Error Handling
		if ($_user->error) error($_user->error);
		
		$response = new stdClass();
		$response->user = $_user->details();
		$response->success = 1;

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Verify Users Email Address					###
	###################################################
	function notifyContact() {
		# Initiate Response
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.contact.xsl';

		# Initiate Customer Object
		$user = new \Register\Customer($_REQUEST['id']);

		# Add Event
		$user->notifyContact(
			array (
				"subject"		=> $_REQUEST['subject'],
				"body"			=> $_REQUEST['body'],
				"from_address"	=> $_REQUEST['from_address'],
				"from_name"		=> $_REQUEST['from_name']
			)
		);

		# Error Handling
		if ($user->error) error($user->error);
		
		$response = new stdClass();
		$response->success = 1;

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Add a New Organization						###
	###################################################
	function addOrganization() {
		# Initiate Response
		$response = new stdClass();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.user.xsl';

		if (! $GLOBALS['_SESSION_']->customer->has_role('register admin')) error("Permission Denied");

		# Initiate Object
		$organization = new \Register\Organization();

		# Add Object
		$organization->add(
			array(
				"name"		=> $_REQUEST['name'],
				"code"		=> $_REQUEST['code'],
			)
		);

		# Error Handling
		if ($organization->error) error($organization->error);
		
		$response->success = 1;
		$response->organization = $organization;

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Get Organization							###
	###################################################
	function getOrganization() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organization.xsl';

		if (isset($_REQUEST['code']))
			if ($GLOBALS['_SESSION_']->customer->has_role('register reporter') || $GLOBALS['_SESSION_']->customer->has_role('register admin') || $GLOBALS['_SESSION_']->organization->code == $_REQUEST['code'])
				$org_code = $_REQUEST['code'];
			else
				error("Permission denied");
		else $org_code = $GLOBALS['_SESSION_']->customer->organization->code;

		# Initiate Organization Object
		$organization = new \Register\Organization();

		# Get Matching Organization
		$organization->get($_REQUEST['code']);

		# Error Handling
		if ($organization->error) error($organization->error);

		$response = new stdClass();
		$response->success = 1;
		$response->organization = $organization;

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Find Organizations							###
	###################################################
	function findOrganizations() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

		if (! $GLOBALS['_SESSION_']->customer->has_role('register reporter') && ! $GLOBALS['_SESSION_']->customer->has_role('register admin')) error('Permission denied');

		# Initiate Organization Object
		$organizationList = new \Register\OrganizationList();

		# Build Query Parameters
		$parameters = array();
		if ($_REQUEST["code"]) $parameters["code"] = $_REQUEST["code"];
		if ($_REQUEST["name"]) $parameters["name"] = $_REQUEST["name"];

		$response = new stdClass();
		$response->request->parameter = $parameters;

		# Get List of Matching Organizations
		$organizations = $organizationList->find($parameters);

		# Error Handling
		if ($organizationList->error) error($organizationList->error);

		$response->success = 1;
		$response->organization = $organizations;

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Find Organization Owned Products			###
	###################################################
	function findOrganizationOwnedProducts() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

		# Build Query Parameters
		$parameters = array();
		if ($_REQUEST['organization']) {
			# Initiate Organization Object
			$_organization = new \Register\Organization();
			$organization = $_organization->get($_REQUEST['organization']);
			if ($_organization->error) app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
			if (! $organization->id) error("Organization not found");
			$parameters['organization_id'] = $organization->id;
		}
		if ($_REQUEST['product']) {
			$_product = new Product();
			$product = $_product->get($_REQUEST['product']);
			if ($_product->error) app_error("Error getting product: ".$_product->error,__FILE__,__LINE__);
			if (! $product->id) error("Product not found");
			$parameters['product_id'] = $product->id;
		}

		$response = new stdClass();
		$response->request->parameter = $parameters;

		# Get List of Matching Products
		$_orgproducts = new OrganizationOwnedProduct();
		$products = $_orgproducts->find($parameters);

		# Error Handling
		if ($_orgproducts->error) app_error($_orgproducts->error,__FILE__,__LINE__);

		$response->success = 1;
		$response->product = $products;

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Get Organization Owned Product				###
	###################################################
	function getOrganizationOwnedProduct() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

		# Initiate Organization Object
		$_organization = new \Register\Organization();
		$organization = $_organization->get($_REQUEST['organization']);
		if ($_organization->error) app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
		if (! $organization->id) error("Organization not found");

		require_once(MODULES."/product/_classes/default.php");
		$_product = new Product();
		$product = $_product->get($_REQUEST['product']);
		if ($_product->error) app_error("Error getting product: ".$_product->error,__FILE__,__LINE__);
		if (! $product->id) error("Product not found");

		$response = new stdClass();

		# Get List of Matching Products
		$_orgproducts = new OrganizationOwnedProduct();
		$product = $_orgproducts->get(
			$organization->id,
			$product->id
		);

		# Error Handling
		if ($_orgproducts->error) app_error($_orgproducts->error,__FILE__,__LINE__);

		$response->success = 1;
		$response->product = $product;

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Add Organization Owned Product				###
	###################################################
	function addOrganizationOwnedProduct() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

		# Initiate Organization Object
		$_organization = new \Register\Organization();
		$organization = $_organization->get($_REQUEST['organization']);
		if ($_organization->error) app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
		if (! $organization->id) error("Organization not found");

		require_once(MODULES."/product/_classes/default.php");
		$_product = new Product();
		$product = $_product->get($_REQUEST['product']);
		if ($_product->error) app_error("Error getting product: ".$_product->error,__FILE__,__LINE__);
		if (! $product->id) error("Product not found");

		$response = new stdClass();

		# Get List of Matching Products
		$_orgproducts = new OrganizationOwnedProduct();
		$products = $_orgproducts->add(
			$organization->id,
			$product->id,
			$_REQUEST['quantity']
		);

		# Error Handling
		if ($_orgproducts->error) app_error($_orgproducts->error,__FILE__,__LINE__);

		$response->success = 1;
		$response->product = $products;

		# Send Response
		header('Content-Type: application/xml');
		print formatOutput($response);
	}
	function expireAgingCustomers() {
		if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
			$expires = strtotime("-12 month", time());
			$date = date('m/d/Y',$expires);
	
			# Initialize Customers
			$customerlist = new \Register\CustomerList();
	
			# Expire Aged Customers
			$count = $customerlist->expire($date);
			if ($customerlist->error) {
				$response->success = 0;
				$response->error = "Error expiring customers: ".$customerlist->error;
			}
			else {
				$response->success = 1;
				$response->message = "$count Customers Expired";
			}
		}
		else {
			$response->success = 0;
			$response->error = "Requires 'register manager' role";
		}

		# Send Response
		print formatOutput($response);
	}
	function expireInactiveOrganizations() {
		if (role('register manager')) {
			$expires = strtotime("-12 month", time());
			$date = date('m/d/Y',$expires);

			# Initialize Organizations
			$organizationlist = new \Register\OrganizationList();

			# Expire Organizations w/o Active Users
			$count = $organizationlist->expire();
			if ($organizationlist->error) {
				$response->success = 0;
				$response->error = "Error expiring organizations: ".$organizationlist->error;
			}
			else {
				$response->success = 1;
				$response->message = "$count Organizations Expired";
			}
		}
		else {
			$response->success = 0;
			$response->error = "Requires 'register manager' role";
		}

		# Send Response
		header('Content-Type: application/xml');
		print formatOutput($response);
	}
	function flagActiveCustomers() {
		$list = new \Register\CustomerList();
		$counter = $list->activate();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->activated = $counter;

		print formatOutput($response);
	}
	function schemaVersion() {
		$schema = new \Register\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new stdClass();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	###################################################
	### System Time									###
	###################################################
	function system_time() {
		return date("Y-m-d H:i:s");
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response->error = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print formatOutput($response,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		}
		else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
?>
