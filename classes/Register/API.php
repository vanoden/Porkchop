<?php
	namespace Register;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'register';
			$this->_version = '0.3.2';
			$this->_release = '2021-06-01';
			$this->_schema = new Schema();
			$this->_admin_role = 'register manager';
			parent::__construct();
		}

        ###################################################
        ### Get Details regarding Current Customer		###
        ###################################################
        function me() {
            # Default StyleSheet
            if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'register.customer.xsl';
            if (!empty($GLOBALS['_SESSION_']->customer) && $GLOBALS['_SESSION_']->customer->can('see admin tools')) $GLOBALS['_SESSION_']->customer->admin = 1;
 
            $siteMessageDeliveryList = new \Site\SiteMessageDeliveryList();
            $siteMessageDeliveryList->find(array('user_id' => $GLOBALS['_SESSION_']->customer->id, 'acknowledged' => false));
            $siteMessagesUnread = $siteMessageDeliveryList->count();

			$me = $GLOBALS['_SESSION_']->customer;
			$me->unreadMessages = $siteMessagesUnread;
			$me->organization = $me->organization();

            $response = new \APIResponse();
			$response->success(true);
            $response->addElement('customer',$me);

            # Send Response
            $response->print();
        }
 
        ###################################################
        ### Authenticate Session						###
        ###################################################
        function authenticateSession() {
            # Default StyleSheet
            if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'register.customer.xsl';

            # Initiate Product Object
            $customer = new \Register\Customer();

			if ($customer->status == 'BLOCKED') error("Your account has been blocked");
			if ($customer->status == 'EXPIRED') error("Your account has expired.  Please use 'forgot password' on the website to restore.");
			if ($customer->auth_failures() >= 3) error("Too many auth failures.  Please use 'forget password' on the website to restore");

            $result = $customer->authenticate($_REQUEST["login"],$_REQUEST["password"]);
            if ($customer->error()) $this->error($customer->error());

            if ($result && $customer->isActive()) {
                app_log("Assigning session ".$GLOBALS['_SESSION_']->id." to customer ".$customer->id,'debug',__FILE__,__LINE__);
                $GLOBALS['_SESSION_']->assign($customer->id);
            }
			elseif ($result) {
				$this->error("This account is not active");
			}
            else {
				$this->_incrementCounter("incorrect");
                app_log("Authentication failed",'notice',__FILE__,__LINE__);
            }

            if (! $result) $this->error("Invalid login password combination");

			$response = new \APIResponse();
			$response->success(true);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Get Details regarding Specified Customer	###
        ###################################################
        function getCustomer() {
            $this->requireAuth();

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.customer.xsl';

            # Initiate Product Object
            if ($_REQUEST["login"] and (! $_REQUEST["code"])) $_REQUEST['code'] = $_REQUEST['login'];
            $customer = new \Register\Customer();
            $customer->get($_REQUEST["code"]);

            if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
                # Can Get Anyone
            }
            elseif ($GLOBALS['_SESSION_']->customer->organization_id == $customer->organization_id) {
                # Can Get Other Members of Your Organization
            }
            else {
                $this->deny();
            }

            # Error Handling
			if (!$customer->exists()) $this->notFound();
            elseif ($customer->error()) $this->error($customer->error());
            else{
                $response = new \APIResponse();
				if ($customer->cached()) $customer->_cached = 1;
                $response->addElement('customer',$customer);
                $response->success = 1;
            }

            # Send Response
            $response->print();
        }
 
        ###################################################
        ### Update Specified Customer					###
        ###################################################
        function updateCustomer() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.customer.xsl';

            if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
                # Can Update Anyone
            }
            elseif ($GLOBALS['_SESSION_']->customer->login = $_REQUEST['code']) {
                # Can Update Yourself
            }
            else {
                $this->deny();
            }

            # Initiate Product Object
            $customer = new \Register\Customer();

            # Find Customer
            $customer->get($_REQUEST['code']);
            if ($customer->error()) $this->app_error("Error getting customer: ".$customer->error(),__FILE__,__LINE__);
            if (! $customer->id) $this->notFound();

            if (isset($_REQUEST['organization']) && !isset($_REQUEST['organization_code'])) {
		        $_REQUEST['organization_code'] = $_REQUEST['organization'];
            }
            if (isset($_REQUEST['organization_code']) && !empty($_REQUEST['organization_code'])) {
                $organization = new \Register\Organization();
                if ($organization->get($_REQUEST['organization_code'])) {
			        $parameters['organization_id'] = $organization->id;
		        }
		        elseif(!empty($organization->error())) {
                    $this->app_error("Error getting organization: ".$organization->error(),__FILE__,__LINE__);
                }
	            else {
                    $this->error("Organization not found");
	            }
            }
            
            if (!empty($_REQUEST['first_name'])) $parameters['first_name'] = noXSS($_REQUEST['first_name']);
            if (!empty($_REQUEST['last_name'])) $parameters['last_name'] = noXSS($_REQUEST['last_name']);
            if (!empty($_REQUEST['password'])) {
				if (!strongPassword($_REQUEST['password'])) $this->error("Password is not complex enough");
				$parameters['password'] = $_REQUEST['password'];
			}
            if (isset($_REQUEST['automation'])) {
                if ($_REQUEST['automation'] == 1) $parameters['automation'] = true;
                else $parameters['automation'] = false;
            }
			if (!empty($_REQUEST['timezone'])) {
				if (!in_array($_REQUEST['timezone'], \DateTimeZone::listIdentifiers())) $this->error("Invalid timezone provided");
				$parameters['timezone'] = $_REQUEST['timezone'];
			}

            # Update Customer
            if (!$customer->update($parameters)) $this->error($customer->error());

            # Error Handling
            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('customer',$customer);

            # Send Response
            $response->print();
        }

        ###################################################
        ### Find Customers								###
        ###################################################
        function findCustomers() {
            # Default StyleSheet
            if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'register.customers.xsl';

            # Build Query Parameters
            $parameters = array();
            if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
                if ($_REQUEST["organization_code"]) {
                    app_log("Getting organization '".$_REQUEST['organization_code']."'",'debug',__FILE__,__LINE__);
                    $organization = new \Register\Organization();
                    $organization->get($_REQUEST["organization_code"]);
                    if ($organization->error()) $this->app_error("Error finding organization: ".$organization->error(),'error',__FILE__,__LINE__);
                    if (! $organization->id) $this->error("Could not find organization '".$_REQUEST["organization_code"]."'");
                    $parameters['organization_id'] = $organization->id;
                }
            }
            elseif (isset($GLOBALS['_SESSION_']->customer->organization()->id) && $GLOBALS['_SESSION_']->customer->organization()->id > 0) {
                $parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization()->id;
            }
			else {
                $this->deny();
            }
            if ($_REQUEST["code"]) $parameters["code"] = $_REQUEST["code"];
            elseif ($_REQUEST["login"]) $parameters["code"] = $_REQUEST["login"];
            if ($_REQUEST["first_name"]) $parameters["first_name"] = $_REQUEST["first_name"];
            if ($_REQUEST["last_name"]) $parameters["last_name"] = $_REQUEST["last_name"];

            # Get List of Matching Customers
            $customerlist = new \Register\CustomerList();
            $customers = $customerlist->find($parameters);

            # Error Handling
            if ($customerlist->error()) $this->error($customerlist->error());

            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('customer',$customers);

            # Send Response
            $response->print();
        }

        /**
         * check if a login exists already for user creating a new account
         */
        function checkLoginNotTaken() {
            $customer = new \Register\Customer();    	
            if ($customer->get($_REQUEST["login"])) print "0";
            else print "1";
        }

        /**
         * check if password is strong enought
         */
        function checkPasswordStrength() {
            $person = new \Register\Customer();    	
            $strength = $person->password_strength($_REQUEST["password"]);
            $minPasswordStrength = 8;
            if (isset($GLOBALS['_config']->register->minimum_password_strength)) $minPasswordStrength = $GLOBALS['_config']->register->minimum_password_strength;
            if ($strength >= $minPasswordStrength) {
                print "1";
            } else {
                print "0";
            }
        }

        ###################################################
        ### Find Roles									###
        ###################################################
        function findRoles() {
            if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) $this->deny();

            $roleList = new \Register\RoleList();
            $roles = $roleList->find();
            
            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('role',$roles);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Find Role Members							###
        ###################################################
        function findRoleMembers() {
            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.rolemembers.xsl';

            if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) $this->deny();

            # Initiate Role Object
            $role = new \Register\Role();
            $role->get($_REQUEST['code']);

            # Get List of Matching Admins
            $admins = $role->members();

            # Error Handling
            if ($role->error()) $this->error($role->error());

			$response = new \APIResponse();
            $response->success(1);
            $response->addElement('admin',$admins);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Add a User Role								###
        ###################################################
        function addRole() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            if (! $GLOBALS['_SESSION_']->customer->can('manage privileges')) $this->deny();

            $role = new \Register\Role();
            $result = $role->add(
                array(
                    'name'	=> $_REQUEST['name'],
                    'description'	=> $_REQUEST['description']
                )
            );
            if ($role->error()) $this->error($role->error());

            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('role',$result);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Update an Existing Role						###
        ###################################################
        function updateRole() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            if (! $GLOBALS['_SESSION_']->customer->can('manage privileges')) $this->deny();

            $response = new \APIResponse();

            $role = new \Register\Role();
            $role->get($_REQUEST['name']);
            if ($role->error()) $this->error($role->error());
            if (! $role->id) $this->error("Role not found");
            $parameters = array();
            if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
            if ($role->update($parameters)) {
                $response->success(true);
            }
            else $this->error($role->error());

            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Add a User to a Role						###
        ###################################################
        function addRoleMember() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) $this->deny();

            $role = new Role();
            $role->get($_REQUEST['name']);
            if ($role->error()) $this->app_error("Error getting role: ".$role->error(),'error',__FILE__,__LINE__);
            if (! $role->id) $this->error("Role not found");
            
            $person = new \Register\Customer();
            $person->get($_REQUEST['login']);
            if ($person->error()) $this->app_error("Error getting person: ".$person->error(),'error',__FILE__,__LINE__);
            if (! $person->id) $this->error("Person not found");

            $result = $role->addMember($person->id);
            if ($role->error()) $this->error($role->error());

            $response = new \APIResponse();
            $response->success(true);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Assign Privilege to Role					###
        ###################################################
        function addRolePrivilege() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            if (! $GLOBALS['_SESSION_']->customer->can('manage privileges')) $this->deny();

            if ($_REQUEST['role']) {
                $role = new \Register\Role();
                $role->get($_REQUEST['role']);
                if ($role->error()) $this->error($role->error());
                if (! $role->id) $this->error("Role not found");
            }
            else {
                $this->error('role required');
            }

            $response = new \APIResponse();
            if ($role->addPrivilege($_REQUEST['privilege'])) {
                $response->success(true);
            }
            else {
                $this->error($role->error());
            }

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Assign Privilege to Role					###
        ###################################################
        function getRolePrivileges() {
            if ($_REQUEST['role']) {
                $role = new \Register\Role();
                $role->get($_REQUEST['role']);
                if ($role->error()) $this->error ($role->error());
                if (! $role->id) $this->error ("Role not found");
            }
            else {
                $this->error('role required');
            }

            $privileges = $role->privileges();

            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('privilege',$privileges);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Does Customer Have Privilege				###
        ###################################################
        function customerHasPrivilege() {
            if ($_REQUEST['login']) {
                $customer = new \Register\Customer();
                $customer->get($_REQUEST['login']);
                if ($customer->error()) $this->error ($customer->error());
                if (! $customer->id) $this->error ("Customer not found");
            }
            else {
                $this->error('login required');
            }

            $response = new \APIResponse();
            $response->success = 1;
            if ($customer->can($_REQUEST['privilege'])) $can = 'yes';
            else $can = 'no';
			$response->addElement('can',$can);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Create Customer Image						###
        ###################################################
        function addImage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            # Authenticated Customer Required
            $this->requireAuth();

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'gallery.image.xsl';

            # Initiate Image Object
            $_image = new \Media\Image();

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
            if ($_image->error()) $this->error($_image->error());
            else{
				$response = new \APIResponse();
                $response->addElement('image',$_image);
                $response->success(true);
            }

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Add a New Customer via Registration			###
        ###################################################
        function addCustomer() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.user.xsl';

            # Initiate Image Object
            $user = new \Register\Customer();
            $user->get($_REQUEST['login']);
            if ($user->id) {
                $this->error("Duplicate Login");
            }

            $organization_id = 0;
            if (!empty($_REQUEST['organization_id'])) {
                $organization = new \Register\Organization($_REQUEST['organization_id']);
                if ($organization->error()) $this->app_error("Error finding organization: ",'error',__FILE__,__LINE__);
                if (! $organization->id) $this->error("Could not find organization by id");
                $organization_id = $organization->id;
            }
            elseif (!empty($_REQUEST['organization'])) {
                $organization = new \Register\Organization();
                $organization->get($_REQUEST['organization']);
                if ($organization->error()) $this->app_error("Error finding organization: ",'error',__FILE__,__LINE__);
                if (! $organization->id) $this->error("Could not find organization");
                $organization_id = $organization->id;
            }

            if (! $_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['code'];
			if (! $user->validLogin($_REQUEST['login'])) $this->error("Login not valid");

            if (isset($_REQUEST['automation'])) {
                if (preg_match('/^(yes|true|1)$/i',$_REQUEST['automation'])) $automation = true;
                else $automation = false;
            }

			$params = array(
				'login'				=> $_REQUEST['login'],
				'custom_1'			=> $_REQUEST['custom_1'],
				'custom_2'			=> $_REQUEST['custom_2'],
			);

			if (!empty($_REQUEST['first_name'])) $params['first_name'] = noXSS($_REQUEST['first_name']);
			if (!empty($_REQUEST['last_name'])) $params['last_name'] = noXSS($_REQUEST['last_name']);
			if (!empty($_REQUEST['password'])) {
				if (!strongPassword($_REQUEST['password'])) $this->error("Password is not complex enough");
				$params['password'] = $_REQUEST['password'];
			}
			if (!empty($automation)) $params['automation'] = $automation;
			if (!empty($organization_id)) $params['organization_id'] = $organization_id;
			if (!empty($_REQUEST['custom_1'])) $params['custom_1'] = $_REQUEST['custom_1'];
			if (!empty($_REQUEST['custom_2'])) $params['custom_2'] = $_REQUEST['custom_2'];

            # Add Event
            $user->add($params);

            # Error Handling
            if ($user->error()) $this->error($user->error());
			$response = new \APIResponse();
            $response->success(true);
            $response->addElement('customer',$user);

            # Send Response
            $response->print();
        }
 
        function findContacts() {
			$this->requireAuth();

            if (isset($_REQUEST['person'])) {
                $customer = new \Register\Customer();
                $customer->get($_REQUEST['person']);
                if ($customer->error()) $this->error($customer->error());
                if (! $customer->id) $this->app_error("Customer not found");
            }

            $parameters = array();
			if ($GLOBALS['_SESSION_']->customer->can("manage customers")) {
				if (isset($customer->id) and $customer->id) $parameters['person_id'] = $customer->id;
			}
			else $parameters['person_id'] = $GLOBALS['_SESSION_']->customer->id;
            if (isset($_REQUEST['type']) and $_REQUEST['type']) $parameters['type'] = $_REQUEST['type'];
            if (isset($_REQUEST['value']) and $_REQUEST['value']) $parameters['value'] = $_REQUEST['value'];
            
            $contactList = new \Register\ContactList();
            $contacts = $contactList->find($parameters);
            if ($contactList->error()) $this->error($contactList->error());
            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('contact',$contacts);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Verify Users Email Address					###
        ###################################################
        function verifyEmail() {
        
            # Initiate Response
            $response = new \APIResponse();
            $response->header('session',$GLOBALS['_SESSION_']->code);
            $response->header('method',$_REQUEST["method"]);

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.verify.xsl';

            # Initiate Image Object
            $user = new Customer();

            if ($user->get($_REQUEST['login'])) {
                if ($user->verify_email($_REQUEST['validation_key'])) {
                    $response->success = 1;
                } else $this->error("Invalid validation key");
            } elseif ($user->error()) $this->error($user->error());
            
            else $this->error("Invalid validation key");

            # Send Response
            $response->print();
        }

        ###################################################
        ### Verify Users Email Address					###
        ###################################################
        function notifyContact() {
			$this->requireAuth();

            # Initiate Response
			$response = new \APIResponse();

            # Initiate Customer Object
            $user = new \Register\Customer($_REQUEST['id']);

			if ($GLOBALS['_SESSION_']->customer->can("manage customers")) {
				// Go Ahead and Send
			}
			elseif($user->exists()) {
				if ($user->organization_id != $GLOBALS['_SESSION_']->customer->organization_id) $this->error("Permission Denied");
			}
			else $this->notFound();

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.contact.xsl';

			$message = new \Email\Message();
			$message->html(true);
			$message->from($_REQUEST['from_address']);
			$message->subject($_REQUEST['subject']);
			$message->body($_REQUEST['body']);

            # Add Event
            $user->notify($message);

            # Error Handling
            if ($user->error()) $this->error($user->error());

            $response->success(true);

            # Send Response
            $response->print();
        }

        ###################################################
        ### Add a New Organization						###
        ###################################################
        function addOrganization() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.user.xsl';

            if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) $this->deny();

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
            if ($organization->error()) $this->error($organization->error());


            # Initiate Response
            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('organization',$organization);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Get Organization							###
        ###################################################
        function getOrganization() {
            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organization.xsl';

            if (isset($_REQUEST['code']))
                if ($GLOBALS['_SESSION_']->customer->can('manage customers') || $GLOBALS['_SESSION_']->customer->organization()->code == $_REQUEST['code'])
                    $org_code = $_REQUEST['code'];
                else
                    $this->deny();
            else $org_code = $GLOBALS['_SESSION_']->customer->organization()->code;

            # Initiate Organization Object
            $organization = new \Register\Organization();

            # Get Matching Organization
            $organization->get($org_code);

            # Error Handling
            if ($organization->error()) $this->error($organization->error());

            $response = new \APIResponse();
            $response->success = 1;
            $response->addElement('organization',$organization);

            # Send Response
            $response->print();
        }

        ###################################################
        ### Find Organizations							###
        ###################################################
        function findOrganizations() {
            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

            if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) $this->deny();

            # Initiate Organization Object
            $organizationList = new \Register\OrganizationList();

            # Build Query Parameters
            $parameters = array();
            if (!empty($_REQUEST["code"])) $parameters["code"] = $_REQUEST["code"];
            if (!empty($_REQUEST["name"])) $parameters["name"] = $_REQUEST["name"];
            if (!empty($_REQUEST["status"])) $parameters["status"] = $_REQUEST["status"];

            # Get List of Matching Organizations
            $organizations = $organizationList->find($parameters);

            # Error Handling
            if ($organizationList->error()) $this->error($organizationList->error());

            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('count',count($organizations));
            $response->addElement('organization',$organizations);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Search Organizations						###
        ###################################################
        function searchOrganizations() {
            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

            if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) $this->deny();

            # Initiate Organization Object
            $organizationList = new \Register\OrganizationList();

            # Build Query Parameters
            $parameters = array();
            $parameters["string"] = $_REQUEST["string"];

            # Get List of Matching Organizations
            $organizations = $organizationList->search($parameters);

            # Error Handling
            if ($organizationList->error()) $this->error($organizationList->error());

			$response = new \APIResponse();
            $response->success(1);
            $response->addElement('organization',$organizations);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Find Organization Owned Products			###
        ###################################################
        function findOrganizationOwnedProducts() {
            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

            # Build Query Parameters
			if (! $GLOBALS['_SESSION_']->customer->can("manage customer products")) {
				$organization = new \Register\Organization($GLOBALS['_SESSION_']->customer->organization_id);
				if (!$organization->exists()) $this->error("Must be associated with an organization");
			}
            elseif (!empty($_REQUEST['organization_code'])) {
                # Initiate Organization Object
                $organization = new \Register\Organization();
                if (! $organization->get($_REQUEST['organization_code'])) $this->error("Organization not found");
                if ($organization->error()) $this->app_error("Error getting organization: ".$organization->error(),__FILE__,__LINE__);
            }
			elseif (!empty($_REQUEST['organization_id'])) {
				$organization = new \Register\Organization($_REQUEST['organization_id']);
			}
            if (!empty($_REQUEST['product_code'])) {
                $product = new \Product\Item();
                if (! $product->get($_REQUEST['product_code'])) $this->error("Product not found");
                if ($product->error()) $this->app_error("Error getting product: ".$product->error(),__FILE__,__LINE__);
            }
			elseif (!empty($_REQUEST['product_id'])) {
				$product = new \Product\Item($_REQUEST['product_id']);
				if (! $product->exists()) $this->error("Product not found");
			}

            $response = new \APIResponse();

            # Get List of Matching Products
            $products = new \Register\Organization\OwnedProduct($organization->id,$product->id);

            # Error Handling
            if ($products->error()) $this->app_error($products->error(),__FILE__,__LINE__);

            $response->success(true);
            $response->addElement('product',$products);

            # Send Response
            $response->print();
        }

        ###################################################
        ### Get Organization Owned Product				###
        ###################################################
        function getOrganizationOwnedProduct() {
			$this->requireAuth();

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

            # Initiate Organization Object
            $organization = new \Register\Organization();
            $organization->get($_REQUEST['organization']);
            if (! $organization->id) $this->error("Organization not found");
            if ($organization->error()) $this->app_error("Error getting organization: ".$organization()->error,__FILE__,__LINE__);

            $product = new \Product\Item();
            $product->get($_REQUEST['product']);
            if ($product->error()) $this->app_error("Error getting product: ".$product->error(),__FILE__,__LINE__);
            if (! $product->id) $this->error("Product not found");

            # Get List of Matching Products
            $product = new \Register\Organization\OwnedProduct($organization->id,$product->id);

            # Error Handling
            if ($product->error()) $this->app_error($product->error(),__FILE__,__LINE__);

            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('product',$product);

            # Send Response
            $response->print();
        }
        
        ###################################################
        ### Add Organization Owned Product				###
        ###################################################
        function addOrganizationOwnedProduct() {
			$this->requireAuth();
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			if (!$GLOBALS['_SESSION_']->customer->can("manage customer credits")) $this->deny();

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organizations.xsl';

            # Initiate Organization Object
            $organization = new \Register\Organization();
            $organization->get($_REQUEST['organization']);
            if ($organization->error()) $this->app_error("Error getting organization: ".$organization->error(),__FILE__,__LINE__);
            if (! $organization->id) $this->notFound("Organization not found");

            $product = new \Product\Item();
            $product->get($_REQUEST['product']);
            if ($product->error()) $this->app_error("Error getting product: ".$product->error(),__FILE__,__LINE__);
            if (! $product->id) $this->notFound("Product not found");

            # Get List of Matching Products
            $_orgproducts = new \Register\Organization\OwnedProduct($organization->id,$product->id);
            $products = $_orgproducts->add(
                $organization->id,
                $product->id,
                $_REQUEST['quantity']
            );

            # Error Handling
            if ($_orgproducts->error()) $this->app_error($_orgproducts->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
            $response->success(true);
            $response->addElement('product',$products);

            # Send Response
            $response->print();
        }
        function findOrganizationLocations() {
            if ($GLOBALS['_SESSION_']->customer->can('manage customers') && isset($_REQUEST['organization_id'])) {
                $organization = new \Register\Organization($_REQUEST['organization_id']);
            }
            elseif ($GLOBALS['_SESSION_']->customer->can('manage customers') && isset($_REQUEST['code'])) {
                $organization = new \Register\Organization();
                $organization->get($_REQUEST['code']);
            }
            else {
                $organization = $GLOBALS['_SESSION_']->customer->organization();
            }
            if (! $organization->id) $this->notFound();

            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('location',$organization->locations(array('recursive' => true)));

            # Send Response
            $response->print();
        }
        function findOrganizationMembers() {
            if ($GLOBALS['_SESSION_']->customer->can('manage customers') && isset($_REQUEST['organization_id'])) {
                $organization = new \Register\Organization($_REQUEST['organization_id']);
            }
            elseif ($GLOBALS['_SESSION_']->customer->can('manage customers') && isset($_REQUEST['code'])) {
                $organization = new \Register\Organization();
                $organization->get($_REQUEST['code']);
            }
            else {
                $organization = $GLOBALS['_SESSION_']->customer->organization();
            }
            if (! $organization->id) $this->error("Organization required");

            $automation = null;
            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('member',$organization->members($_REQUEST['type']));

            # Send Response
            $response->print();
        }
        function findCustomerLocations() {
            if ($GLOBALS['_SESSION_']->customer->canh('manage customers') && isset($_REQUEST['customer_id'])) {
                $customer = new \Register\Customer($_REQUEST['customer_id']);
            }
            elseif ($GLOBALS['_SESSION_']->customer->can('manage customers') && isset($_REQUEST['login'])) {
                $customer = new \Register\Customer();
                $customer->get($_REQUEST['login']);
            }
            else {
                $customer = $GLOBALS['_SESSION_']->customer;
            }
            $response = new \APIResponse();
            $response->success(true);
            $response->addElement('location',$customer->locations(array('recursive' => true)));

            # Send Response
            $response->print();
        }
        function expireAgingCustomers() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			$this->requirePrivilege("manage customers");

            $expires = strtotime("-12 month", time());
            $date = date('m/d/Y',$expires);
        
            # Initialize Customers
            $customerlist = new \Register\CustomerList();
        
            # Expire Aged Customers
			$response = new \APIResponse();
            $count = $customerlist->expire($date);
            if ($customerlist->error()) $this->error("Error expiring customers: ".$customerlist->error());
            else $response->addElement('message',"$count Customers Expired");

            # Send Response
            $response->print();
        }
        
        function expireInactiveOrganizations() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			$this->requirePrivilege("manage customers");

            $expires = strtotime("-12 month", time());
            $date = date('m/d/Y',$expires);

            # Initialize Organizations
            $organizationlist = new \Register\OrganizationList();

            # Expire Organizations w/o Active Users
            $count = $organizationlist->expire();
			$response = new \APIResponse();
            if ($organizationlist->error()) $response->error("Error expiring organizations: ".$organizationlist->error());
            else $response->addElement('message',"$count Organizations Expired");

            # Send Response
            $response->print();
        }
        
        function flagActiveCustomers() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage customers");

            $list = new \Register\CustomerList();
            $counter = $list->flagActive();
            $response = new \APIResponse();
            $response->addElement('activated',$counter);

            # Send Response
            $response->print('json');
        }

        /**
         * get last active date for member
         */
        function getMemberLastActive() {
			$this->requireAuth();
            $user = new \Register\Customer($_REQUEST['memberId']);
			if ($user->error()) $this->error($user->error());
			if (! $user->exists()) $this->notFound();

            $response = new \APIResponse();
            $response->addElement('memberId',$_REQUEST['memberId']);
            $response->addElement('lastActive',$user->last_active());

            # Send Response
            $response->print('json');
        }

        /**
         * search registered organizations by name
         */
        function searchOrganizationsByName() {
			$this->requireAuth();
            $organizationList = new \Register\OrganizationList();
            $search = array();
            $search['string'] = $_REQUEST['term'];
            $search['_like'] = array('name');
            $search['status'] = array('NEW','ACTIVE','EXPIRED');
            $organizationsFound = $organizationList->search($search);
            
            $results = array();
            foreach ($organizationsFound as $organization) {
                $newOrganization = new \stdClass();
                $newOrganization->id = $organization->id;
                $newOrganization->label = (($organization->status == "EXPIRED") ? " * EXPIRED * " : "") . $organization->name;
                $newOrganization->value = $organization->name;
                $results[] = $newOrganization;
            }
            print $this->formatOutput($results,'json');
        }

        /**
         * get shipment by serial number
         */
        function shipmentFindBySerial() {
            $supportShipmentItem = new \Support\ShipmentItem();
            $shipmentDetails = $supportShipmentItem->findBySerial($_REQUEST['serialNumber']);
            print $this->formatOutput($shipmentDetails,'json');
        }

        function findLocations() {
            $parameters = array();
            if (isset($_REQUEST['organization']) && $GLOBALS['_SESSION_']->customer->can("manage customers")) {
                $organization = new \Register\Organization();
                if (!$organization->get($_REQUEST['organization'])) $this->notFound("Organization not found");
                $_REQUEST['organization_id'] = $organization->id;
            }
            elseif (isset($_REQUEST['organization_id']) && $GLOBALS['_SESSION_']->customer->can('manage customers')) {
                $parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization()->id;
            }
            else {
                $parameters['organization_id'] = $_REQUEST['organization_id'];
            }
            $parameters['recursive'] = true;

            $locationList = new \Register\LocationList();
            $locations = $locationList->find($parameters);

			$response = new \APIResponse();
            $response->addElement('location',$locations);
			$response->print();
        }

        function addLocation() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			$this->requireAuth();

            $parameters = new \stdClass;
            $parameters->name = $_REQUEST['name'];
            $parameters->address_1 = $_REQUEST['address_1'];
            $parameters->address_2 = $_REQUEST['address_2'];
            $parameters->city = $_REQUEST['city'];
            $parameters->zip_code = $_REQUEST['zip_code'];

            if ($_REQUEST['admin_id']) {
                $parameters->admin_id = $_REQUEST['admin_id'];
            }
            elseif ($_REQUEST['admin_code']) {
                $admin = new Admin();
                if (! $admin->get($_REQUEST['admin_code'])) {
                    $this->notFound("Province not found");
                    return false;
                }
            }
            $province = new \Geography\Province();
            if (! $province->get($admin->id,$_REQUEST['province'])) $this->error("Province not found");
            $parameters->province_id = $province->id;

            $location = new \Register\Location();
            if ($location->add($parameters)) {
				$response = new \APIResponse();
                $response->addElement('location',$location);
                $response->print();
            }
            else {
                $this->error("Cannot add location: ".$location->error());
            }
        }

        public function findPrivileges() {
			$this->requirePrivilege("manage customers");
            $privilegeList = new \Register\PrivilegeList();
            $privileges = $privilegeList->find();
			if ($privilegeList->error()) $this->error($privilegeList->error());

			$response = new \APIResponse();
			$response->addElement('privilege',$privileges);

            $response->print();
        }

		public function findPrivilegePeers() {
			$this->requirePrivilege('manage customers');
			if (isset($_REQUEST['privilege_name'])) {
				$privilege = new \Register\Privilege();
				if (! $privilege->get($_REQUEST['privilege_name'])) error("Privilege not found");
			}
			elseif (isset($_REQUEST['privilege_id'])) {
				$privilege = new \Register\Privilege($_REQUEST['privilege_id']);
				if (! $privilege->id) error ("Privilege not found");
			}
			else {
				error("privilege_id or privilege_name required");
			}

			$people = $privilege->peers();
			if ($privilege->error()) error("Error getting peers: ".$privilege->error());

			$response = new \APIResponse();
			$response->addElement('customer',$people);

			$response->print();
		}

		function getLocation() {

		}

        public function findPendingRegistrations() {
			$this->requirePrivilege("manage customers");
            $queue = new \Register\Queue();
            $parameters = array();

            if (!empty($_REQUEST['status'])) {
				if (! $queue->validStatus($_REQUEST['status'])) $this->error("Invalid status");
				else $parameters['status'] = $_REQUEST['status'];
			}

			$queueList = new \Register\QueueList();
			$pendingCustomers = $queueList->find($parameters);
			if ($queueList->error()) $this->error($queueList->error());

			$response = new \APIResponse();
			$response->addElement('registration',$pendingCustomers);
			$response->print();
        }

        public function getPendingRegistration() {
			$this->requirePrivilege("manage customers");
            $queue = new \Register\Queue();
            $parameters = array();

            if (empty($_REQUEST['login'])) $this->error("login required");
			if (! $queue->validCode($_REQUEST['login'])) $this->error("invalid login");
			if (! $queue->get($_REQUEST['login'])) $this->error("Registration not found");

			$response = new \APIResponse();
			$response->addElement('registration',$queue);
			$response->print();
        }

        public function getRegistrationVerificationURL() {
			$this->requirePrivilege("manage customers");

			$person = new \Register\Customer();
            if (empty($_REQUEST['login'])) $this->error("login required");
			if (! $person->validCode($_REQUEST['login'])) $this->error("invalid login");
			if (! $person->get($_REQUEST['login'])) $this->notFound("Registration not found");

			$response = new \APIResponse();
			$response->addElement('url',"/_register/new_customer?method=verify&login=".$person->login."&access=".$person->validationKey());
			if ($person->error()) $this->error($person->error());
			$response->print();
        }

        public function getEmailValidationURL() {
			$this->requirePrivilege("manage customers");
            $person = new \Register\Customer();
            $parameters = array();

            if (empty($_REQUEST['login'])) $this->error("login required");
			if (! $person->validCode($_REQUEST['login'])) $this->error("invalid login");
			if (! $person->get($_REQUEST['login'])) $this->notFound("Registration not found");

			$response = new \APIResponse();
			$response->addElement('url',"/_register/validate?login=".$person->login."&validation_key=".$person->validationKey());
			if ($person->error()) $this->error($person->error());
			$response->print();
        }

        public function getPasswordResetURL() {
			$this->requirePrivilege("manage customers");
            $person = new \Register\Customer();

            if (empty($_REQUEST['login'])) $this->error("login required");
			if (! $person->validCode($_REQUEST['login'])) $this->error("invalid login");
			if (! $person->get($_REQUEST['login'])) $this->notFound("Registration not found");
			$key = $person->resetKey();
			if ($person->error()) $this->error($person->error());
			if (empty($key)) $this->error("No key found");

			$response = new \APIResponse();
			$response->addElement('url',"/_register/reset_password?token=$key");
			if ($person->error()) $this->error($person->error());
			$response->print();
        }
		
		public function _methods() {
			$queue = new \Register\Queue();
			return array(
				'ping'	=> array(),
				'me'	=> array(
				),
				'authenticateSession'	=> array(
					'login'			=> array('required' => true),
					'password'		=> array('required' => true)
				),
				'getCustomer'	=> array(
					'login' 	=> array('required' => true),
				),
				'updateCustomer'	=> array(
					'code'			=> array('required' => true),
					'organization'	=> array(),
					'first_name'	=> array(),
					'last_name'		=> array(),
					'password'  	=> array(),
					'automation'	=> array(),
					'timezone'		=> array()
				),
				'findCustomers'	=> array(
					'organization_code' => array(),
					'login'     	=> array(),
					'first_name'	=> array(),
					'last_name'		=> array(),
				),
				'findOrganizations'	=> array(
					'organization_code'	=> array(),
					'organization_id' => array()
				),
				'findOrganizationOwnedProducts'	=> array(
					'organization_code'	=> array(),
					'organization_id' => array(),
					'product_code'	=> array(),
					'product_id' => array()
				),
				'findRoles'	    => array(),
				'findRoleMembers'	=> array(
					'code'	=> array('required' => true)
				),
				'addRoleMember'	=> array(
					'login'		=> array('required' => true),
					'name'		=> array('required' => true)
				),
				'findPrivileges'	=> array(),
				'findPrivilegePeers'	=> array(
					'privilege_name'	=> array('required' => true)
				),
				'findPendingRegistrations' => array(
					'status'	=> array(
						'options' => $queue->statii()
					)
				),
				'getPendingRegistration' => array(
					'login'	=> array('required' => true)
				),
				'acceptTermsOfUse'	=> array(
					'tou_code'	=> array('required' => true),
					'tou_version'	=> array('required' => true),
				),
				'declineTermsOfUse'	=> array(
					'tou_code'	=> array('required' => true),
					'tou_version'	=> array('required' => true),
				)
			);
		}
	}
