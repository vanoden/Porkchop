<?php
	namespace Product;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'product';
			$this->_version = '0.2.0';
			$this->_release = '2020-02-04';
			$this->_schema = new \Product\Schema();
			parent::__construct();
		}

		###################################################
		### Add an Item									###
		###################################################
		public function addItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			$product = new \Product\Item();
	
			$product->add(
				array(
					'code'			=> $_REQUEST['code'],
					'name'			=> $_REQUEST['name'],
					'description'	=> $_REQUEST['description'],
					'status'		=> $_REQUEST['status'],
					'type'			=> $_REQUEST['type']
				)
			);
			if ($product->error) $this->error("Error adding product: ".$product->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $product;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Product							###
		###################################################
		public function updateItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");

			$product = new \Product\Item();
			$product->get($_REQUEST['code']);
			if ($product->error) $this->error("Error finding product: ".$product->error);
			if (! $product->id) $this->error("Product not found");
	        $productStatus = $_REQUEST['status'];
	        if (empty($productStatus)) $productStatus = 'ACTIVE';
	        $productDescription = $_REQUEST['description'];
	        if (empty($productDescription)) $productDescription = '';
	        
			$product->update(
				array(
					'name'			=> $_REQUEST['name'],
					'type'			=> $_REQUEST['type'],
					'status'		=> $productStatus,
					'description'	=> $productDescription,
				)
			);
			if ($product->error) $this->error("Error updating product: ".$product->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $product;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Specified Product						###
		###################################################
		public function getItem() {
			if (isset($_REQUEST['id'])) {
				$product = new \Product\Item($_REQUEST['id']);
			}
			else {
				$product = new \Product\Item();
				$product->get($_REQUEST['code']);
			}
	
			if ($product->error) $this->error("Error getting product: ".$product->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $product;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Product						###
		###################################################
		public function findItems() {
			$productlist = new \Product\ItemList();
			$parameters = array();
			if (isset($_REQUEST['code'])) $parameters["code"] = $_REQUEST['code'];
			if (isset($_REQUEST['name'])) $parameters["name"] = $_REQUEST['name'];
			if (isset($_REQUEST['status'])) $parameters["status"] = $_REQUEST['status'];
			if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			$products = $productlist->find($parameters);
			if ($productlist->error) $this->error("Error finding products: ".$productlist->error);
			$this->response->success = 1;
			$this->response->product = $products;
	
			print $this->formatOutput($this->response);
		}
	
        ###################################################
        ### Add a Price                                 ###
        ###################################################
        public function addPrice() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
            $parameters = array();
            $product = new \Product\Item();
            if (! $product->get($_REQUEST['product_code'])) $this->error("Product not found");
            $parameters['product_id'] = $product->id;
            if (! preg_match('/^\d[\d\.]*$/',$_REQUEST['amount'])) $this->error("Valid price required");
            $parameters['amount'] = $_REQUEST['amount'];
			if (preg_match('/^(ACTIVE|INACTIVE)$/i',$_REQUEST['status'])) $parameters['status'] = strtoupper($_REQUEST['status']);

            if (isset($_REQUEST['date_active']) && get_mysql_date($_REQUEST['date_active'])) {
                $parameters['date_active'] = get_mysql_date($_REQUEST['date_active']);
            }
            elseif (isset($_REQUEST['date_active'])) $this->error("Invalid date_active");
            else $parameters['date_active'] = get_mysql_date(time());

            if (! $product->addPrice($parameters)) $this->error($product->error());
            $this->response->success = 1;
            print $this->formatOutput($this->response);
        }

        ###################################################
        ### Get a Product Price                         ###
        ###################################################
        public function getPrice() {
            $parameters = array();
            $product = new \Product\Item();
            if (! $product->get($_REQUEST['product_code'])) $this->error("Product not found");
            $parameters['product_id'] = $product->id;
            $price =  $product->getPrice($parameters);
            if ($product->error()) $this->error($product->error());
            $this->response->success = 1;
            $this->response->price = $price;
            print $this->formatOutput($this->response);
        }

		###################################################
		### Add a Relationship							###
		###################################################
		public function addRelationship() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			$_product = new \Product\Item();
			if (defined($_REQUEST['parent_code'])) {
				$parent = $_product->get($_REQUEST['parent_code']);
				if (! $parent->id) $this->error("Parent product '".$_REQUEST['parent_code']."' not found");
				$_REQUEST['parent_id'] = $parent->id;
			}
			if ($_REQUEST['child_code']) {
				$child = $_product->get($_REQUEST['child_code']);
				if (! $child->id) $this->error("Child product '".$_REQUEST['child_code']."' not found");
				$_REQUEST['child_id'] = $child->id;
			}
			if (! $_REQUEST['child_id'])
				error("child_id or valid child_code required");
	
			$relationship = new \Product\Relationship();
			$relationship->add(
				array(
					'parent_id'	=> $_REQUEST['parent_id'],
					'child_id'	=> $_REQUEST['child_id'],
				)
			);
			if ($relationship->error) $this->error("Error adding relationship: ".$relationship->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->relationship = $relationship;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get a Relationship							###
		###################################################
		public function getRelationship() {
			if ($_REQUEST['parent_code']) {
				$parent = new \Product\Item();
				if ($parent->get($_REQUEST['parent_code'])) $_REQUEST['parent_id'] = $parent->id;
				else $this->error("Parent not found");
			}
			if ($_REQUEST['child_code']) {
				$child = new \Product\Item();
				if ($child->get($_REQUEST['child_code'])) $_REQUEST['child_id'] = $child->id;
				else $this->error("Child not found");
			}
			if (! $_REQUEST['child_id'])
				error("child_id or valid child_code required");
	
			$relationship = new \Product\Relationship();
			$relationship->get($_REQUEST['parent_id'],$_REQUEST['child_id']);
	
			if ($relationship->error) $this->error("Error getting relationship: ".$relationship->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->relationship = $relationship;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find Relationships							###
		###################################################
		public function findRelationships() {
			$_product = new \Product\Item();
			if ($_REQUEST['parent_code'])
			{
				$parent = $_product->get($_REQUEST['parent_code']);
				$_REQUEST['parent_id'] = $parent->id;
			}
			if ($_REQUEST['child_code'])
			{
				$child = $_product->get($_REQUEST['child_code']);
				$_REQUEST['child_id'] = $child->id;
			}
			if (preg_match('/^\d+$/',$_REQUEST['parent_id'])) $parameters['parent_id'] = $_REQUEST['parent_id'];
			if ($_REQUEST['child_id']) $parameters['child_id'] = $_REQUEST['child_id'];
			
			$_relationship = new \Product\Relationship();
			$relationships = $_relationship->find($parameters);
	
			if ($_relationship->error) $this->error("Error finding relationships: ".$_relationship->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->relationship = $relationships;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Add a Group									###
		###################################################
		public function addGroup() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			$_REQUEST['type'] = 'group';
			$this->addItem();
		}
	
		###################################################
		### Update a Group								###
		###################################################
		public function updateGroup() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			$this->updateItem();
		}
	
		###################################################
		### Find matching Group							###
		###################################################
		public function findGroups() {
			$_REQUEST['type'] = 'group';
			$this->findItems();
		}
	
		###################################################
		### Add Product to Group						###
		###################################################
		public function addGroupItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['group_code'])) $this->error("group_code required for addGroupItem method");
			if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['item_code'])) $this->error("group_code required for addGroupItem method");
	
			$group = new \Product\Group();
			if (!$group->get($_REQUEST['group_code'])) $this->error("Error finding group: ".$group->error);
			if (! $group->id) $this->error("Group not found");
	
			$item = new \Product\Item();
			if (!$item->get($_REQUEST['item_code'])) $this->error("Error finding item: ".$item->error);
			if (!$item->id) $this->error("Item not found");
	
			$group->addItem($item);
			if ($group->error) $this->error("Error adding item to group: ".$group->error);
			$response = new \HTTP\Response();
			$response->success = 1;
	
			print $this->formatOutput($response);
		}

		###################################################
		### Find Products in Group						###
		###################################################
		public function findGroupItems() {
			$group = new \Product\Group();
			if (!$group->get($_REQUEST['code'])) $this->error("Product Group Not Found");
			if ($group->error()) $this->error("Error finding group: ".$group->error());
			if (! $group->id) $this->error("Group not found");

			$items = $group->items();
			if ($group->error()) $this->error("Error finding items: ".$group->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->item = $items;

			print $this->formatOutput($response);
		}

		###################################################
		### Add a Product Intance						###
		###################################################
		public function addInstance() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! preg_match('/^[\w\-\_\.\:\(\)]+$/',$_REQUEST['code']))
			 $this->error("code required to add instance");
	
			if (isset($_REQUEST['organization'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
					$organization = new \Register\Organization($_REQUEST['organization_id']);
				}
				else {
				 $this->error("No permissions to see other organizations data");
				}
			}
			else {
				$organization = $GLOBALS['_SESSION_']->customer->organization();
			}
	
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if ($product->error) {
				$this->app_error("Error finding product: ".$product->error,__FILE__,__LINE__);
			 $this->error("No product found matching '".$_REQUEST['product_code']."'");
			}
	
			$organization = new \Register\Organization($_REQUEST['organization_id']);
			if ($organization->error) $this->app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
			if (! $organization->id) $this->error("No organization found matching '".$_REQUEST['organization']);
	
			$instance = new \Product\Instance();
			if ($instance->error) $this->app_error("Error initializing instance: ".$instance->error,__FILE__,__LINE__);
			$instance->add(
				array(
					'code'				=> $_REQUEST['code'],
					'product_id'		=> $product->id,
					'organization_id'	=> $organization->id,
					'name'				=> $_REQUEST['code']
				)
			);
			if ($instance->error) $this->error("Error adding instance: ".$instance->error);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->instance = $instance;
	
			print $this->formatOutput($response);
		}

		###################################################
		### Get Specified Instance						###
		###################################################
		public function getInstance() {
			if (isset($_REQUEST['id'])) {
				$instance = new \Product\Instance($_REQUEST['id']);
			}
			elseif (isset($_REQUEST['product_id'])) {
				$instance = new \Product\Instance();
				if ($instance->error) $this->app_error("Error initializing instance: ".$instance->error,__FILE__,__LINE__);
				$instance->get($_REQUEST['code'],$_REQUEST['product_id']);
			}
			else {
				$instance = new \Product\Instance();
				if ($instance->error) $this->app_error("Error initializing instance: ".$instance->error,__FILE__,__LINE__);
	
				$instance->getSimple($_REQUEST['code']);
				if ($instance->error) $this->app_error("Error finding instance(s): ".$instance->error,__FILE__,__LINE__);
			}
			if (! $GLOBALS['_SESSION_']->customer->can('manage product instances') && $instance->organization_id != $instance->organization_id)
				$this->app_error("Permission Denied");
	
			$response = new \HTTP\Response();
			if (isset($instance->code)) {
				$response->success = 1;
				$response->instance = $instance;
			} else {
				$response->success = '0';
				$response->error = 'Instance '.$_REQUEST['code'].' not found';
				$response->instance = $instance;
			}
	
			print $this->formatOutput($response);
		}

		###################################################
		### Update an Instance							###
		###################################################
		public function updateInstance() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$instance = new \Product\Instance();
			if ($instance->error) $this->app_error("Error initializing asset: ".$instance->error,__FILE__,__LINE__);
			if (isset($_REQUEST['product_code']) && strlen($_REQUEST['product_code'])) {
				$product = new \Product\Item();
				$product->get($_REQUEST['product_code']);
				$instance->get($_REQUEST['code'],$product->id);
			}
			else {
				$instance->getSimple($_REQUEST['code']);
			}
			if ($instance->error) $this->app_error("Error finding instance: ".$instance->error,__FILE__,__LINE__);
			if (! $instance->id) $this->error("Instance not found");
	
			$parameters = array();
			if ($_REQUEST['name'])
				$parameters['name'] = $_REQUEST['name'];
		
			if (isset($_REQUEST['organization'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
					$organization = new \Register\Organization();
					$organization->get($_REQUEST['organization_code']);
					if ($organization->error) $this->app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
					$parameters['organization_id'] = $organization->id;
				}
				else {
				 $this->error("No permissions to specify another organization");
				}
			}

			$instance->update($parameters);
			if ($instance->error) $this->app_error("Error updating instance: ".$instance->error,__FILE__,__LINE__);

			$this->response->success = 1;
			$this->response->instance = $instance;

			print $this->formatOutput($this->response);
		}

		###################################################
		### Find matching Instances						###
		###################################################
		public function findInstances() {
			$instancelist = new \Product\InstanceList();
			if ($instancelist->error) $this->app_error("Error initializing instance list: ".$instancelist->error,__FILE__,__LINE__);
	
			$parameters = array();
			if (isset($_REQUEST['code']))
				$parameters['code'] = $_REQUEST['code'];
	
			if (isset($_REQUEST['name']))
				$parameters['name'] = $_REQUEST['name'];
	
			if (isset($_REQUEST['product_code']) && strlen($_REQUEST['product_code'])) {
				$product = new \Product\Item();
				$product->get($_REQUEST['product_code']);
				if ($product->error) $this->app_error("Error finding product: ".$product->error,__FILE__,__LINE__);
				if (! $product->id) $this->error("Product not found");
				$parameters['product_id'] = $product->id;
			}
			if (isset($_REQUEST['organization_code']) && strlen($_REQUEST['organization_code'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage product instances') && $GLOBALS['_SESSION_']->customer->can('manage customers')) {
					$organization = new \Register\Organization();
					$organization->get($_REQUEST['organization_code']);
					if ($organization->error) $this->app_error("Error finding organization: ".$organization->error,__FILE__,__LINE__);
					$parameters['organization_id'] = $organization->id;
				} else {
					app_log("Unauthorized attempt to access instances from another organization",'notice',__FILE__,__LINE__);
				    $this->error("Permission Denied");
				}
			}
			elseif(! $GLOBALS['_SESSION_']->customer->can('manage product instances')) {
				$parameters['organization_id'] = $GLOBALS['_SESSION_']->customer->organization()->id;
			} else {
				# Privileges support access
			}
	
			$instances = $instancelist->find($parameters);
			if ($instancelist->error) $this->app_error("Error initializing instance(s): ".$instancelist->error,__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->instance = $instances;

			print $this->formatOutput($response);
		}

		###################################################
		### Add Image to Product						###
		###################################################
		public function addItemImage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			# Load Media Module
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if ($product->error) app_error("Error finding product: ".$product->error,__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");
	
			$_image = new \Media\Item();
			$image = $_image->get($_REQUEST['image_code']);
			if ($_image->error) app_error("Error finding image: ".$_image->error,__FILE__,__LINE__);
			if (! $image->id) $this->error("Image not found");
	
	
			$product->addImage($product->id,$image->id,$_REQUEST['label']);
			if ($product->error) app_error("Error adding image: ".$product->error,__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
	
			print $this->formatOutput($response);
		}
		public function addProductImage() {
			$this->addItemImage();
		}
		
		###################################################
		### Add Metadata to Product						###
		###################################################
		public function addItemMetadata() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			$_product = new \Product\Item();
			$product = $_product->get($_REQUEST['code']);
			if ($_product->error) app_error("Error finding product: ".$_product->error,__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");
	
			$_product->addMeta($product->id,$_REQUEST['key'],$_REQUEST['value']);
			if ($_product->error) app_error("Error adding metadata: ".$_product->error,__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
	
			print $this->formatOutput($response);
		}
		
		public function addProductMeta() {
			$this->addItemMetadata();
		}
	
		public function _methods() {
			return array(
				'ping'			=> array(),
				'findItems'	=> array(
					'code'		=> array(),
					'name'		=> array(),
					'status'	=> array(),
					'type'		=> array(),
				),
				'getItem'	=> array(
					'code'	=> array(),
				),
				'addItem'	=> array(
					'code'		=> array('required' => true),
					'name'		=> array('required' => true),
					'status'	=> array('default' => 'ACTIVE'),
					'type'		=> array('required' => true),
				),
				'updateItem'	=> array(
					'code'		=> array('required' => true),
					'name'		=> array(),
					'status'	=> array(),
					'type'		=> array(),
				),
                'addPrice'      => array(
                    'product_code'  => array('required' => true),
                    'amount'        => array('required' => true),
                    'date_active'   => array('default' => get_mysql_date(time())),
                    'status'        => array('required' => true,'default' => 'ACTIVE','options' => array('INACTIVE','ACTIVE')),
                ),
				'getPrice'		=> array(
					'product_code'	=> array('required' => true)
				),
				'findRelationships'	=> array(
					'parent_code'	=> array('required' => true),
					'child_code'	=> array('required' => true),
				),
				'addRelationship'	=> array(
					'parent_code'	=> array('required' => true),
					'child_code'	=> array('required' => true),
				),
				'getRelationship'	=> array(
					'parent_code'	=> array('required' => true),
					'child_code'	=> array('required' => true),
				),
				'findGroupItems'	=> array(
					'code'			=> array('required' => true)
				),
				'getInstance'	=> array(
					'code'		=> array('required' => true),
				),
				'addInstance'	=> array(
					'code'		=> array('required' => true),
					'product_code'	=> array(),
					'name'		=> array(),
					'organization_id'	=> array(),
				),
				'updateInstance'	=> array(
					'code'		=> array('required'	=> true),
					'product_code'	=> array(),
					'name'		=> array(),
					'organization_id'	=> array(),
				),
				'findInstances'	=> array(
					'code'		=> array(),
					'product_code'	=> array(),
					'organization_code'	=> array(),
					'name'		=> array(),
				),
			);
		}
	}
