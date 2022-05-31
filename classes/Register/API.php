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
            if ($GLOBALS['_SESSION_']->customer->can('see admin tools')) $GLOBALS['_SESSION_']->customer->admin = 1;
 
            $siteMessageDeliveryList = new \Site\SiteMessageDeliveryList();
            $siteMessageDeliveryList->find(array('user_id' => $GLOBALS['_SESSION_']->customer->id, 'acknowledged' => false));
            $siteMessagesUnread = $siteMessageDeliveryList->count();
            $GLOBALS['_SESSION_']->customer->unreadMessages = $siteMessagesUnread;
            $response = new \HTTP\Response();
            $response->customer = $GLOBALS['_SESSION_']->customer;
            $response->success = 1;

            # Send Response
            api_log($response);
            print $this->formatOutput($response);
        }
 
        ###################################################
        ### Authenticate Session						###
        ###################################################
        function authenticateSession() {
            # Default StyleSheet
            if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'register.customer.xsl';

            # Initiate Product Object
            $customer = new \Register\Customer();

            $result = $customer->authenticate($_REQUEST["login"],$_REQUEST["password"]);
            if ($customer->error) $this->error($customer->error);

            if ($result > 0) {
                app_log("Assigning session ".$GLOBALS['_SESSION_']->id." to customer ".$customer->id,'debug',__FILE__,__LINE__);
                $GLOBALS['_SESSION_']->assign($customer->id);
            }
            else {
				$this->_incrementCounter("incorrect");
                app_log("Authentication failed",'notice',__FILE__,__LINE__);
            }
            
            $this->response->success = $result;
            if (! $result) $this->error("Invalid login password combination");

            # Send Response
            print $this->formatOutput($this->response);
        }
        
        ###################################################
        ### Get Details regarding Specified Customer	###
        ###################################################
        function getCustomer() {
            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.customer.xsl';

            # Initiate Product Object
            $customer = new \Register\Customer();

            if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
                # Can Get Anyone
            }
            elseif ($GLOBALS['_SESSION_']->customer->id = $customer->id) {
                # Can Get Yourself
            }
            else {
                $this->deny();
            }

            if ($_REQUEST["login"] and (! $_REQUEST["code"])) $_REQUEST['code'] = $_REQUEST['login'];
            $customer->get($_REQUEST["code"]);

            # Error Handling
            if ($customer->error) $this->error($customer->error);
            else{
                $response = new \HTTP\Response();
                $response->customer = $customer;
                $response->success = 1;
            }

            # Send Response
            print $this->formatOutput($response);
        }
 
        ###################################################
        ### Update Specified Customer					###
        ###################################################
        function updateCustomer() {
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
            if ($customer->error) $this->app_error("Error getting customer: ".$customer->error,__FILE__,__LINE__);
            if (! $customer->id) $this->error("Customer not found");

            if ($_REQUEST['organization']) {
                $_organization = new \Register\Organization();
                $organization = $_organization->get($_REQUEST['organization']);
                if ($_organization->error) $this->app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
                if (! $organization->id) $this->error("Organization not found");
                $parameters['organization_id'] = $organization->id;
            }
            
            if (isset($_REQUEST['first_name'])) $parameters['first_name'] = $_REQUEST['first_name'];
            if (isset($_REQUEST['last_name'])) $parameters['last_name'] = $_REQUEST['last_name'];
            if (isset($_REQUEST['password'])) $parameters['password'] = $_REQUEST['password'];
            if (isset($_REQUEST['automation'])) {
                if ($_REQUEST['automation'] == 1) $parameters['automation'] = true;
                else $parameters['automation'] = false;
            }

            # Update Customer
            $customer->update($parameters);

            # Error Handling
            if ($customer->error) $this->app_error("Error updating customer: ".$customer->error,__FILE__,__LINE__);
            $response = new \HTTP\Response();
            $response->customer = $customer;
            $response->success = 1;

            # Send Response
            print $this->formatOutput($response);
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
                    if ($organization->error) $this->app_error("Error finding organization: ".$organization->error,'error',__FILE__,__LINE__);
                    if (! $organization->id) $this->error("Could not find organization '".$_REQUEST["organization_code"]."'");
                    $parameters['organization_id'] = $organization->id;
                }
            }
            elseif (isset($GLOBALS['_SESSION_']->customer->organization->id)) {
                $parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
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
            if ($customerlist->error) $this->error($customerlist->error);

            $response = new \HTTP\Response();
            $response->success = 1;
            $response->customer = $customers;

            # Send Response
            print $this->formatOutput($response);
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
            $person = new \Register\Person();    	
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
            
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->role = $roles;
            
            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Find Role Members							###
        ###################################################
        function findRoleMembers() {
            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.rolemembers.xsl';

            if (! $GLOBALS['_SESSION_']->customer->can('manage customers'));

            # Initiate Role Object
            $role = new \Register\Role();
            $role->get($_REQUEST['code']);

            # Get List of Matching Admins
            $admins = $role->members();

            # Error Handling
            if ($role->error) $this->error($role->error);

            $this->response->success = 1;
            $this->response->admin = $admins;

            # Send Response
            print $this->formatOutput($this->response);
        }
        
        ###################################################
        ### Add a User Role								###
        ###################################################
        function addRole() {
            if (! $GLOBALS['_SESSION_']->customer->can('manage privileges')) $this->deny();

            $role = new \Register\Role();
            $result = $role->add(
                array(
                    'name'	=> $_REQUEST['name'],
                    'description'	=> $_REQUEST['description']
                )
            );
            if ($role->error) $this->error($role->error);

            $response = new \HTTP\Response();
            $response->success = 1;
            $response->role = $result;

            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Update an Existing Role						###
        ###################################################
        function updateRole() {
            if (! $GLOBALS['_SESSION_']->customer->can('manage privileges')) $this->deny();

            $response = new \HTTP\Response();

            $role = new \Register\Role();
            $role->get($_REQUEST['name']);
            if ($role->error) $this->error($role->error);
            if (! $role->id) $this->error("Role not found");
            $parameters = array();
            if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
            if ($role->update($parameters)) {
                $response->success = 1;
            }
            else {
                $response->success = 0;
                $response->error = $role->error;
            }
            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Add a User to a Role						###
        ###################################################
        function addRoleMember() {
            if (! $GLOBALS['_SESSION_']->customer->can('manage customers')) $this->deny();

            $role = new Role();
            $role->get($_REQUEST['name']);
            if ($role->error) $this->app_error("Error getting role: ".$role->error,'error',__FILE__,__LINE__);
            if (! $role->id) $this->error("Role not found");
            
            $person = new \Register\Customer();
            $person->get($_REQUEST['login']);
            if ($person->error) $this->app_error("Error getting person: ".$person->error,'error',__FILE__,__LINE__);
            if (! $person->id) $this->error("Person not found");

            $result = $role->addMember($person->id);
            if ($role->error) $this->error($role->error);

            $response = new \HTTP\Response();
            $response->success = 1;

            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Assign Privilege to Role					###
        ###################################################
        function addRolePrivilege() {
            if (! $GLOBALS['_SESSION_']->customer->can('manage privileges')) $this->deny();

            if ($_REQUEST['role']) {
                $role = new \Register\Role();
                $role->get($_REQUEST['role']);
                if ($role->error) $this->error($role->error);
                if (! $role->id) $this->error("Role not found");
            }
            else {
                $this->error('role required');
            }

            $response = new \HTTP\Response();
            if ($role->addPrivilege($_REQUEST['privilege'])) {
                $response->success = 1;
            }
            else {
                $this->error($role->error);
            }

            # Send Response
            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Assign Privilege to Role					###
        ###################################################
        function getRolePrivileges() {
            if ($_REQUEST['role']) {
                $role = new \Register\Role();
                $role->get($_REQUEST['role']);
                if ($role->error) $this->error ($role->error);
                if (! $role->id) $this->error ("Role not found");
            }
            else {
                $this->error('role required');
            }

            $privileges = $role->privileges();

            $response = new \HTTP\Response();
            $response->success = 1;
            $response->privilege = $privileges;

            # Send Response
            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Does Customer Have Privilege				###
        ###################################################
        function customerHasPrivilege() {
            if ($_REQUEST['login']) {
                $customer = new \Register\Customer();
                $customer->get($_REQUEST['login']);
                if ($customer->error) $this->error ($customer->error);
                if (! $customer->id) $this->error ("Customer not found");
            }
            else {
                $this->error('login required');
            }

            $response = new \HTTP\Response();
            $response->success = 1;
            if ($customer->can($_REQUEST['privilege'])) $response->can = 'yes';
            else $response->can = 'no';

            # Send Response
            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Create Customer Image						###
        ###################################################
        function addImage() {
            # Authenticated Customer Required
            #confirm_customer();

            # Initiate Response
            $response = new \HTTP\Response();
            $response->header->session = $GLOBALS['_SESSION_']->code;
            $response->header->method = $_REQUEST["method"];

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
            if ($_image->error) $this->error($_image->error);
            else{
                $response->image = $_image->details();
                $response->success = 1;
            }

            # Send Response
            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Add a New Customer via Registration			###
        ###################################################
        function addCustomer() {
            # Initiate Response
            $this->response->header->session = $GLOBALS['_SESSION_']->code;
            $this->response->header->method = $_REQUEST["method"];

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.user.xsl';

            # Initiate Image Object
            $user = new \Register\Customer();
            $user->get($_REQUEST['login']);
            if ($user->id) {
                $this->error("Duplicate Login");
            }

            $organization_id = 0;
            if ($_REQUEST['organization_id']) {
                $organization = new \Register\Organization($_REQUEST['organization_id']);
                if ($organization->error) $this->app_error("Error finding organization: ",'error',__FILE__,__LINE__);
                if (! $organization->id) $this->error("Could not find organization by id");
                $organization_id = $organization->id;
            }
            elseif ($_REQUEST['organization']) {
                $organization = new \Register\Organization();
                $organization->get($_REQUEST['organization']);
                if ($organization->error) $this->app_error("Error finding organization: ",'error',__FILE__,__LINE__);
                if (! $organization->id) $this->error("Could not find organization");
                $organization_id = $organization->id;
            }

            if (! $_REQUEST['login']) $_REQUEST['login'] = $_REQUEST['code'];
            if (isset($_REQUEST['automation'])) {
                if ($_REQUEST['automation'] == 1) $automation = true;
                else $automation = false;
            }

            # Add Event
            $user->add(
                array(
                    'first_name'		=> $_REQUEST['first_name'],
                    'last_name'		    => $_REQUEST['last_name'],
                    'login'			    => $_REQUEST['login'],
                    'password'		    => $_REQUEST['password'],
                    'organization_id'	=> $organization_id,
                    'custom_1'		    => $_REQUEST['custom_1'],
                    'custom_2'		    => $_REQUEST['custom_2'],
                    'automation'		=> $automation,
                )
            );

            # Error Handling
            if ($user->error) $this->error($user->error);
            $this->response->customer = $user;
            $this->response->success = 1;

            # Send Response
            print $this->formatOutput($this->response);
        }
 
        function findContacts() {
            if (isset($_REQUEST['person'])) {
                $customer = new \Register\Customer();
                $customer->get($_REQUEST['person']);
                if ($customer->error) $this->error($customer->error);
                if (! $customer->id) $this->app_error("Customer not found");
            }

            $parameters = array();
            if (isset($customer->id) and $customer->id) $parameters['person_id'] = $customer->id;
            if (isset($_REQUEST['type']) and $_REQUEST['type']) $parameters['type'] = $_REQUEST['type'];
            if (isset($_REQUEST['value']) and $_REQUEST['value']) $parameters['value'] = $_REQUEST['value'];
            
            $contactList = new \Register\ContactList();
            $contacts = $contactList->find($parameters);
            if ($contactList->error) $this->error($contactList->error);
            $response = new \HTTP\Response();
            $response->contact = $contacts;
            $response->success = 1;
            

            # Send Response
            print $this->formatOutput($response,array());
        }
        
        ###################################################
        ### Verify Users Email Address					###
        ###################################################
        function verifyEmail() {
        
            # Initiate Response
            $response = new \HTTP\Response();
            $response->header->session = $GLOBALS['_SESSION_']->code;
            $response->header->method = $_REQUEST["method"];

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.verify.xsl';

            # Initiate Image Object
            $user = new Customer();

            if ($user->get($_REQUEST['login'])) {
                if ($user->verify_email($_REQUEST['validation_key'])) {
                    $response->success = 1;
                } else $this->error("Invalid validation key");
            } elseif ($user->error) $this->error($user->error);
            
            else $this->error("Invalid validation key");

            # Send Response
            print $this->formatOutput($response);
        }

        ###################################################
        ### Verify Users Email Address					###
        ###################################################
        function notifyContact() {
            # Initiate Response
            $this->response->header->session = $GLOBALS['_SESSION_']->code;
            $this->response->header->method = $_REQUEST["method"];

            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'register.contact.xsl';

            # Initiate Customer Object
            $user = new \Register\Customer($_REQUEST['id']);

			$message = new \Email\Message();
			$message->html(true);
			$message->from($_REQUEST['from_address']);
			$message->subject($_REQUEST['subject']);
			$message->body($_REQUEST['body']);

            # Add Event
            $user->notify($message);

            # Error Handling
            if ($user->error) $this->error($user->error);

            $this->response->success = 1;

            # Send Response
            print $this->formatOutput($this->response);
        }

        ###################################################
        ### Add a New Organization						###
        ###################################################
        function addOrganization() {
            # Initiate Response
            $response = new \HTTP\Response();
            $response->header->session = $GLOBALS['_SESSION_']->code;
            $response->header->method = $_REQUEST["method"];

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
            if ($organization->error) $this->error($organization->error);
            
            $response->success = 1;
            $response->organization = $organization;

            # Send Response
            print $this->formatOutput($response);
        }
        
        ###################################################
        ### Get Organization							###
        ###################################################
        function getOrganization() {
            # Default StyleSheet
            if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'customer.organization.xsl';

            if (isset($_REQUEST['code']))
                if ($GLOBALS['_SESSION_']->customer->can('manage customers') || $GLOBALS['_SESSION_']->organization->code == $_REQUEST['code'])
                    $org_code = $_REQUEST['code'];
                else
                    $this->deny();
            else $org_code = $GLOBALS['_SESSION_']->customer->organization->code;

            # Initiate Organization Object
            $organization = new \Register\Organization();

            # Get Matching Organization
            $organization->get($_REQUEST['code']);

            # Error Handling
            if ($organization->error) $this->error($organization->error);

            $response = new \HTTP\Response();
            $response->success = 1;
            $response->organization = $organization;

            # Send Response
            print $this->formatOutput($response);
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

            $response = new \HTTP\Response();
            $response->request->parameter = $parameters;

            # Get List of Matching Organizations
            $organizations = $organizationList->find($parameters);

            # Error Handling
            if ($organizationList->error) $this->error($organizationList->error);

            $response->success = 1;
            $response->count = count($organizations);
            $response->organization = $organizations;

            # Send Response
            print $this->formatOutput($response);
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

            $response = new \HTTP\Response();
            $response->request->parameter = $parameters;

            # Get List of Matching Organizations
            $organizations = $organizationList->search($parameters);

            # Error Handling
            if ($organizationList->error) $this->error($organizationList->error);

            $response->success = 1;
            $response->organization = $organizations;

            # Send Response
            print $this->formatOutput($response);
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
                if ($_organization->error) $this->app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
                if (! $organization->id) $this->error("Organization not found");
                $parameters['organization_id'] = $organization->id;
            }
            if ($_REQUEST['product']) {
                $_product = new \Product\Item();
                $product = $_product->get($_REQUEST['product']);
                if ($_product->error) $this->app_error("Error getting product: ".$_product->error,__FILE__,__LINE__);
                if (! $product->id) $this->error("Product not found");
                $parameters['product_id'] = $product->id;
            }

            $response = new \HTTP\Response();
            $response->request->parameter = $parameters;

            # Get List of Matching Products
            $products = new \Register\Organization\OwnedProduct($parameters['organization_id'],$parameters['product_id']);

            # Error Handling
            if ($products->error) $this->app_error($products->error,__FILE__,__LINE__);

            $response->success = 1;
            $response->product = $products;

            # Send Response
            print $this->formatOutput($response);
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
            if ($_organization->error) $this->app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
            if (! $organization->id) $this->error("Organization not found");

            require_once(MODULES."/product/_classes/default.php");
            $_product = new \Product\Item();
            $product = $_product->get($_REQUEST['product']);
            if ($_product->error) $this->app_error("Error getting product: ".$_product->error,__FILE__,__LINE__);
            if (! $product->id) $this->error("Product not found");

            $response = new \HTTP\Response();

            # Get List of Matching Products
            $product = new \Register\Organization\OwnedProduct($organization->id,$product->id);

            # Error Handling
            if ($product->error) $this->app_error($product->error,__FILE__,__LINE__);

            $response->success = 1;
            $response->product = $product;

            # Send Response
            print $this->formatOutput($response);
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
            if ($_organization->error) $this->app_error("Error getting organization: ".$_organization->error,__FILE__,__LINE__);
            if (! $organization->id) $this->error("Organization not found");

            require_once(MODULES."/product/_classes/default.php");
            $_product = new \Product\Item();
            $product = $_product->get($_REQUEST['product']);
            if ($_product->error) $this->app_error("Error getting product: ".$_product->error,__FILE__,__LINE__);
            if (! $product->id) $this->error("Product not found");

            $response = new \HTTP\Response();

            # Get List of Matching Products
            $_orgproducts = new \Register\Organization\OwnedProduct($organization->id,$product->id);
            $products = $_orgproducts->add(
                $organization->id,
                $product->id,
                $_REQUEST['quantity']
            );

            # Error Handling
            if ($_orgproducts->error) $this->app_error($_orgproducts->error,__FILE__,__LINE__);

            $response->success = 1;
            $response->product = $products;

            # Send Response
            header('Content-Type: application/xml');
            print $this->formatOutput($response);
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
                $organization = $GLOBALS['_SESSION_']->customer->organization;
            }
            if (! $organization->id) $this->error("Organization required");
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->location = $organization->locations(array('recursive' => true));

            print $this->formatOutput($response);
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
                $organization = $GLOBALS['_SESSION_']->customer->organization;
            }
            if (! $organization->id) $this->error("Organization required");

            $automation = null;
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->member = $organization->members($_REQUEST['type']);

            print $this->formatOutput($response);
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
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->location = $customer->locations(array('recursive' => true));

            print $this->formatOutput($response);
        }
        function expireAgingCustomers() {
            $response = new \HTTP\Response();
            if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
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
            print $this->formatOutput($response);
        }
        
        function expireInactiveOrganizations() {
            $response = new \HTTP\Response();
            if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
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
            print $this->formatOutput($response);
        }
        
        function flagActiveCustomers() {
            $list = new \Register\CustomerList();
            $counter = $list->flagActive();
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->activated = $counter;

            print $this->formatOutput($response);
        }

        /**
         * get last active date for member
         */
        function getMemberLastActive() {
            $user = new \Register\Customer($_REQUEST['memberId']);
            $response = new \HTTP\Response();
            $results = new \stdClass();
            $results->memberId = $_REQUEST['memberId'];
            $results->lastActive = $user->last_active();
            print $this->formatOutput($results,'json');
        }

        /**
         * search registered organizations by name
         */
        function searchOrganizationsByName() {
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
                $newOrganization->label = $organization->name;
                $newOrganization->value = $organization->name;
                $results[] = $newOrganization;
            }
            print $this->formatOutput($results,'json');
        }

        /**
         * get shipment by serial number
         */
        function shipmentFindBySerial() {
            header('Content-Type: application/json');
            $supportShipmentItem = new \Support\ShipmentItem();
            $shipmentDetails = $supportShipmentItem->findBySerial($_REQUEST['serialNumber']);
            print $this->formatOutput($shipmentDetails,'json');
        }

        function findLocations() {
            $response = new \HTTP\Response();
            $parameters = array();
            if (isset($_REQUEST['organization']) && $GLOBALS['_SESSION_']->customer->can("manage customers")) {
                $organization = new \Register\Organization();
                if (!$organization->get($_REQUEST['organization'])) $this->error("Organization not found");
                $_REQUEST['organization_id'] = $organization->id;
            }
            elseif (isset($_REQUEST['organization_id']) && $GLOBALS['_SESSION_']->customer->can('manage customers')) {
                $parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization->id;
            }
            else {
                $parameters['organization_id'] = $_REQUEST['organization_id'];
            }
            $parameters['recursive'] = true;

            $locationList = new \Register\LocationList();
            $locations = $locationList->find($parameters);

            $response->success = 1;
            $response->location = $locations;

            print $this->formatOutput($response);
        }

        function addLocation() {
            $response = new \HTTP\Response();
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
                    $this->error = "Admin not found";
                    return false;
                }
            }
            $province = new \Geography\Province();
            if (! $province->get($admin->id,$_REQUEST['province'])) $this->error("Province not found");
            $parameters->province_id = $province->id;

            $location = new \Register\Location();
            if ($location->add($parameters)) {
                $response->success = 1;
                $response->location = $location;
                print $this->formatOutput($response);
            }
            else {
                $this->error("Cannot add location: ".$location->error());
            }
        }

        public function findPrivileges() {
            $privilegeList = new \Register\PrivilegeList();
            $privileges = $privilegeList->find();
            return $privileges;
        }

        function getLocation() {
            
        }
		
		public function _methods() {
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
				),
				'findCustomers'	=> array(
                    'organization_code' => array(),
					'login'     	=> array(),
					'first_name'	=> array(),
					'last_name'		=> array(),
				),
				'findRoles'	    => array(),
				'findRoleMembers'	=> array(
					'code'	=> array()
				)
			);
		}
	}
