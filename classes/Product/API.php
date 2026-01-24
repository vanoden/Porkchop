<?php
	namespace Product;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'product';
			$this->_version = '0.3.3';
			$this->_release = '2023-09-12';
			$this->_schema = new \Product\Schema();
			parent::__construct();
		}

		/** @method addItem()
		 * Add a new Product
		 * Takes values from $_REQUEST
		 * Required: code, name, type
		 * Optional: description, status
		 * 
		 * @return void
		 */
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
			if ($product->error()) $this->error("Error adding product: ".$product->error());

			$response = new \APIResponse();
			$response->addElement('item',$product);
			$response->print();
		}
	
		/** @method updateItem()
		 * Update an existing Product's details
		 * Takes values from $_REQUEST
		 * @return void
		 */
		public function updateItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");

			$product = new \Product\Item();
			$product->get($_REQUEST['code']);
			if ($product->error()) $this->error("Error finding product: ".$product->error());
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
			if ($product->error()) $this->error("Error updating product: ".$product->error());

			$response = new \APIResponse();
			$response->addElement('item',$product);
			$response->print();
		}

		/** @method getItem()
		 * Get Specified Product with id or code
		 * Takes values from $_REQUEST
		 * Required: id or code
		 * @return void
		 */
		public function getItem() {
			if (isset($_REQUEST['id'])) {
				$product = new \Product\Item($_REQUEST['id']);
			}
			else {
				$product = new \Product\Item();
				$product->get($_REQUEST['code']);
			}
	
			if ($product->error()) $this->error("Error getting product: ".$product->error());

			$responseObj = $product->_clone();
			if ($product->exists()) {
				$responseObj->metadata = $product->getAllMetadata();
			}
			else {
				$this->notFound();
			}

			$response = new \APIResponse();
			$response->addElement('item',$responseObj);
			$response->print();
		}

		/** @method findItems()
		 * Find matching Product Items given various criteria
		 */
		public function findItems() {
			$productlist = new \Product\ItemList();
			$parameters = array();
			if (isset($_REQUEST['code'])) $parameters["code"] = $_REQUEST['code'];
			if (isset($_REQUEST['name'])) $parameters["name"] = $_REQUEST['name'];
			if (isset($_REQUEST['status'])) $parameters["status"] = $_REQUEST['status'];
			if (isset($_REQUEST['type']) && !empty($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			if (!empty($_REQUEST['variant_type'])) $parameters['variant_type'] = $_REQUEST['variant_type'];
			// BaseListClass control params: _sort, _order, _limit, _offset
			if (isset($_REQUEST['_sort'])) $parameters['_sort'] = $_REQUEST['_sort'];
			if (isset($_REQUEST['_order'])) $parameters['_order'] = $_REQUEST['_order'];
			if (isset($_REQUEST['_limit'])) $parameters['_limit'] = $_REQUEST['_limit'];
			if (isset($_REQUEST['_offset'])) $parameters['_offset'] = $_REQUEST['_offset'];
			$products = $productlist->find($parameters);
			if ($productlist->error()) $this->error("Error finding products: ".$productlist->error());
	
			$response = new \APIResponse();
			$response->addElement('item',$products);
			$response->print();
		}

		/** @method setVisibility()
		 * Set the visibility of a Product Item in a specific realm
		 * @param productVisibilityRealm $realm The visibility realm
		 * @param bool $visible True to make visible, false to hide
		 * @return void
		 */
		public function setVisibility() {
			$product = new \Product\Item();
			$product->get($_REQUEST['code']);
			if ($product->error()) $this->error("Error finding product: ".$product->error());
			if (! $product->exists()) $this->error("Product not found");
			app_log("Setting visibility for product '".$_REQUEST['code']."' in realm '".$_REQUEST['realm']."' to '".$_REQUEST['visibility']."'",'info',__FILE__,__LINE__);
			$realm = constant(\productVisibilityRealm::class . '::' . $_REQUEST['realm']);
			if ($realm === null) {
				$this->error("Invalid realm specified");
			}
			if (preg_match('/^(true|1|yes|on)$/i',$_REQUEST['visibility'])) {
				$visible = true;
			}
			elseif (preg_match('/^(false|0|no|off)$/i',$_REQUEST['visibility'])) {
				$visible = false;
			}
			else {
				$this->error("Invalid visible value specified");
			}
			if (! $product->setVisibility($realm, $visible)) {
				$this->error("Error setting visibility: ".$product->error());
			}

			$response = new \APIResponse();
			$response->addElement('visible', $visible);
			$response->addElement('realm', $_REQUEST['realm']);
			$response->addElement('byte', $product->visibility);
			$response->print();
		}

		/** @method getVisibility()
		 * Get the visibility of a Product Item in a specific realm
		 * @param productVisibilityRealm $realm The visibility realm
		 * @return bool True if visible, false otherwise
		 */
		public function getVisibility() {
			$product = new \Product\Item();
			$product->get($_REQUEST['code']);
			if ($product->error()) $this->error("Error finding product: ".$product->error());
			if (! $product->exists()) $this->error("Product '".$_REQUEST['code']."' not found");
			$realm = constant(\productVisibilityRealm::class . '::' . $_REQUEST['realm']);
			if ($realm === null) {
				$this->error("Invalid realm specified");
			}
			$visible = $product->getVisibility($realm);

			$response = new \APIResponse();
			$response->addElement('visible', $visible);
			$response->addElement('realm', $_REQUEST['realm']);
			$response->addElement('byte', $product->visibility);
			$response->print();
		}

        /** @method addPrice()
		 * Add a price to a Product Item
		 */
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

            $response = new \APIResponse();
			$response->print();
        }

		/** @method getPrice()
		 * Get a Product Item Price
		 */
        public function getPrice() {
            $parameters = array();
            $product = new \Product\Item();
            if (! $product->get($_REQUEST['product_code'])) $this->error("Product not found");
            $parameters['product_id'] = $product->id;
            $price =  $product->getPrice($parameters);
            if ($product->error()) $this->error($product->error());

			$response = new \APIResponse();
			$response->addElement('price',$price);
			$response->print();
        }

		/** @method addRelationship()
		 * Add a relationship between two Product Items
		 */
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
			if ($relationship->error()) $this->error("Error adding relationship: ".$relationship->error());

			$response = new \APIResponse();
			$response->addElement('relationship',$relationship);
			$response->print();
		}

		/** @method getRelationship()
		 * Get a relationship between two Product Items
		 */
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

			if ($relationship->error()) $this->error("Error getting relationship: ".$relationship->error());

			$response = new \APIResponse();
			$response->addElement('relationship',$relationship);
			$response->print();
		}

		/** @method findRelationships()
		 * Find relationships between Product Items given various criteria
		 */
		public function findRelationships() {
			$product = new \Product\Item();
			if ($_REQUEST['parent_code']) {
				if ($product->get($_REQUEST['parent_code']))$_REQUEST['parent_id'] = $product->id;
                else $this->error("Parent product not found");
			}
			if ($_REQUEST['child_code']) {
				$child = $product->get($_REQUEST['child_code']);
				$_REQUEST['child_id'] = $child->id;
			}
			if (preg_match('/^\d+$/',$_REQUEST['parent_id'])) $parameters['parent_id'] = $_REQUEST['parent_id'];
			if ($_REQUEST['child_id']) $parameters['child_id'] = $_REQUEST['child_id'];
			
			$relationshipList = new \Product\RelationshipList();
			$relationships = $relationshipList->find($parameters);
	
			if ($relationshipList->error()) $this->error("Error finding relationships: ".$relationshipList->error());

			$response = new \APIResponse();
			$response->addElement('relationship',$relationships);
			$response->print();
		}

		/** @method addGroup()
		 * Add a new Product Item of type 'group'
		 */
		public function addGroup() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			$_REQUEST['type'] = 'group';
			$this->addItem();
		}

		/** @method updateGroup()
		 * Update a Product Item of type 'group'
		 */
		public function updateGroup() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			$this->updateItem();
		}

		/** @method findGroups()
		 * Find matching Product Items of type 'group'
		 */
		public function findGroups() {
			$_REQUEST['type'] = 'group';
			$this->findItems();
		}

		/** @method addGroupItem()
		 * Add a Product Item to a Group
		 */
		public function addGroupItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");
			if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['group_code'])) $this->error("group_code required for addGroupItem method");
			if (! preg_match('/^[\w\-\.\_\s]+$/',$_REQUEST['item_code'])) $this->error("group_code required for addGroupItem method");
	
			$group = new \Product\Group();
			if (!$group->get($_REQUEST['group_code'])) $this->error("Error finding group: ".$group->error());
			if (! $group->id) $this->error("Group not found");
	
			$item = new \Product\Item();
			if (!$item->get($_REQUEST['item_code'])) $this->error("Error finding item: ".$item->error());
			if (!$item->id) $this->error("Item not found");
	
			$group->addItem($item);
			if ($group->error()) $this->error("Error adding item to group: ".$group->error());

			$response = new \APIResponse();
			$response->print();
		}

		/** @method findGroupItems()
		 * Find Products in Group
		 */
		public function findGroupItems() {
			$group = new \Product\Group();
			if (!$group->get($_REQUEST['code'])) $this->error("Product Group Not Found");
			if ($group->error()) $this->error("Error finding group: ".$group->error());
			if (! $group->id) $this->error("Group not found");

			$items = $group->items();
			if ($group->error()) $this->error("Error finding items: ".$group->error());

			$response = new \APIResponse();
			$response->addElement('relationship',$items);
			$response->print();
		}

		/** @method addInstance()
		 * Add a new Product Instance
		 */
		public function addInstance() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! preg_match('/^[\w\-\_\.\:\(\)]+$/',$_REQUEST['code']))
			 $this->error("code required to add instance");
	
			if (isset($_REQUEST['organization_id'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
					$organization = new \Register\Organization($_REQUEST['organization_id']);
				}
				else {
					$this->error("No permissions to see other organizations data");
				}
			}
			elseif (isset($_REQUEST['organization_code'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
					$organization = new \Register\Organization();
					$organization->get($_REQUEST['organization_code']);
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
			if ($product->error()) {
				$this->app_error("Error finding product: ".$product->error(),__FILE__,__LINE__);
				$this->error("No product found matching '".$_REQUEST['product_code']."'");
			}
	
			$instance = new \Product\Instance();
			if ($instance->error()) $this->app_error("Error initializing instance: ".$instance->error(),__FILE__,__LINE__);
			$instance->add(
				array(
					'code'				=> $_REQUEST['code'],
					'product_id'		=> $product->id,
					'organization_id'	=> $organization->id,
					'name'				=> $_REQUEST['code']
				)
			);
			if ($instance->error()) $this->error("Error adding instance: ".$instance->error());

			$response = new \APIResponse();
			$response->addElement('instance',$instance);
			$response->print();
		}

		/** @method getInstance()
		 * Get a specified Product Instance
		 */
		public function getInstance() {
			if (isset($_REQUEST['id'])) {
				$instance = new \Product\Instance($_REQUEST['id']);
			}
			elseif (isset($_REQUEST['product_id'])) {
				$instance = new \Product\Instance();
				if ($instance->error()) $this->app_error("Error initializing instance: ".$instance->error(),__FILE__,__LINE__);
				$instance->get($_REQUEST['code'],$_REQUEST['product_id']);
			}
			else {
				$instance = new \Product\Instance();
				if ($instance->error()) $this->app_error("Error initializing instance: ".$instance->error(),__FILE__,__LINE__);
	
				$instance->getSimple($_REQUEST['code']);
				if ($instance->error()) $this->app_error("Error finding instance(s): ".$instance->error(),__FILE__,__LINE__);
			}
			if (! $GLOBALS['_SESSION_']->customer->can('manage product instances') && $instance->organization_id != $instance->organization_id)
				$this->app_error("Permission Denied");

			if (isset($instance->code)) {
                $response = new \APIResponse();
				$response->addElement('instance',$instance);
                $response->print();
			} else {
				$this->error('Instance '.$_REQUEST['code'].' not found');
			}
		}

		/** @method updateInstance()
		 * Update a specified Product Instance
		 */
		public function updateInstance() {
			$instance = new \Product\Instance();
			if ($instance->error()) $this->app_error("Error initializing asset: ".$instance->error(),__FILE__,__LINE__);
			if (isset($_REQUEST['product_code']) && strlen($_REQUEST['product_code'])) {
				$product = new \Product\Item();
				$product->get($_REQUEST['product_code']);
				if (! $product->exists()) $this->error("Product not found");
				$instance->get($_REQUEST['code'],$product->id);
				if (! $instance->exists()) $this->error("Instance not found");
			}
			else {
				$instance->getSimple($_REQUEST['code']);
			}
			if ($instance->error()) $this->app_error("Error finding instance: ".$instance->error(),__FILE__,__LINE__);
			if (! $instance->id) $this->error("Instance not found");
	
			$parameters = array();
			if ($_REQUEST['name'])
				$parameters['name'] = $_REQUEST['name'];

			if (!empty($_REQUEST['organization'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
					$organization = new \Register\Organization();
					$organization->get($_REQUEST['organization_code']);
					if ($organization->error()) $this->app_error("Error finding organization: ".$organization->error(),__FILE__,__LINE__);
					$parameters['organization_id'] = $organization->id;
				}
				else {
				 $this->error("No permissions to specify another organization");
				}
			}

			if ($_REQUEST['new_product_code'] && strlen($_REQUEST['new_product_code'])) {
				$product = new \Product\Item();
				$product->get($_REQUEST['new_product_code']);
				if ($product->error()) $this->app_error("Error finding product: ".$product->error(),__FILE__,__LINE__);
				if (! $product->id) $this->error("Product not found");
				$parameters['product_id'] = $product->id;
			}

			$instance->update($parameters);
			if ($instance->error()) $this->app_error("Error updating instance: ".$instance->error(),__FILE__,__LINE__);

            $response = new \APIResponse();
            $response->addElement('instance',$instance);
            $response->print();
		}

		/** @method findInstances()
		 * Find matching Product Instances
		 */
		public function findInstances() {
			$instancelist = new \Product\InstanceList();
			if ($instancelist->error()) $this->app_error("Error initializing instance list: ".$instancelist->error(),__FILE__,__LINE__);
	
			$parameters = array();
			if (isset($_REQUEST['code']))
				$parameters['code'] = $_REQUEST['code'];
	
			if (isset($_REQUEST['name']))
				$parameters['name'] = $_REQUEST['name'];
	
			if (isset($_REQUEST['product_code']) && strlen($_REQUEST['product_code'])) {
				$product = new \Product\Item();
				$product->get($_REQUEST['product_code']);
				if ($product->error()) $this->app_error("Error finding product: ".$product->error(),__FILE__,__LINE__);
				if (! $product->id) $this->error("Product not found");
				$parameters['product_id'] = $product->id;
			}
			if (isset($_REQUEST['organization_code']) && strlen($_REQUEST['organization_code'])) {
				if ($GLOBALS['_SESSION_']->customer->can('manage product instances') && $GLOBALS['_SESSION_']->customer->can('manage customers')) {
					$organization = new \Register\Organization();
					$organization->get($_REQUEST['organization_code']);
					if ($organization->error()) $this->app_error("Error finding organization: ".$organization->error(),__FILE__,__LINE__);
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
			if ($instancelist->error()) $this->app_error("Error initializing instance(s): ".$instancelist->error(),__FILE__,__LINE__);
            $response = new \APIResponse();
            $response->addElement('instance',$instances);
            $response->print();
		}

		/** @method changeInstanceCode()
		 * Change the code (serial number) of an existing product instance
		 * Takes values from $_REQUEST
		 * Required: code, new_code, reason
		 * Optional: product_id
		 * @return void 
		 */
		public function changeInstanceCode() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege("manage products");

			$product = new \Product\Item();
			$instance = new \Product\Instance();

			if (!$product->validCode($_REQUEST['product_code'])) $this->error("Valid product code required");

			if (!$instance->validCode($_REQUEST['code'])) $this->error("Valid code required");
			if (!$instance->validCode($_REQUEST['new_code'])) $this->error("Valid code required");
			if (empty($_REQUEST['reason'])) $this->error("Reason required");
	
			if (! $product->get($_REQUEST['product_code'])) $this->error("Product not found");

			$instance->get($_REQUEST['code'],$product->id);
			if ($instance->error()) $this->error("Error finding product instance: ".$instance->error());
			if (! $instance->id) $this->error("Instance "+$_REQUEST['code']+" of "+$product->code+" not found");

			$instance->changeCode($_REQUEST['new_code'],$_REQUEST['reason']);
			if ($instance->error()) $this->error("Error changing instance code: ".$instance->error());
			$instance->details();

			$response = new \APIResponse();
			$response->addElement('instance',$instance);
			$response->print();
		}

		/** @method addItemImage()
		 * Assign an image to an existing product
		 */
		public function addItemImage() {
			// Identify Product by Code or ID
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if ($product->error()) app_log("Error finding product: ".$product->error(),'error',__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");
			
			// Identify Image by Code or ID
			$image = new \Media\Item();
			$image->get($_REQUEST['image_code']);
			if ($image->error()) app_log("Error finding image: ".$image->error(),'error',__FILE__,__LINE__);
			if (! $image->id) $this->error("Image not found");

			// Default Label to Image Code if not given
			if (! isset($_REQUEST['label']) || ! strlen($_REQUEST['label'])) $_REQUEST['label'] = $image->name;

            // Force object_type to Spectros\Product\Item for consistency in object_images
            $product->addImage($image->id, 'Spectros\\Product\\Item', isset($_REQUEST['label']) ? $_REQUEST['label'] : '');
			if ($product->error()) app_log("Error adding image: ".$product->error(),'error',__FILE__,__LINE__);

			// Assemble and Deliver Response
            $response = new \APIResponse();
			$response->addElement('product',$product);
            $response->print();
		}

		/** @method addProductImage()
		 * DEPRECATED
		 * Alias for addItemImage()
		 */
		public function addProductImage() {
			$this->addItemImage();
		}

		/** @method dropItemImage()
		 * Dissassociate an Image from a Product
		 */
		public function dropItemImage() {
			// Identify Product by Code or ID
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if ($product->error()) app_error("Error finding product: ".$product->error(),__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");

			// Identify Image by Code or ID
			$image = new \Media\Item();
			$image->get($_REQUEST['image_code']);
			if ($image->error()) app_error("Error finding image: ".$image->error(),__FILE__,__LINE__);
			if (! $image->id) $this->error("Image not found");

			// Remove Image from Product
			$product->removeImage($image->id);
			if ($product->error()) app_error("Error removing image: ".$product->error(),__FILE__,__LINE__);

			// Assemble and Deliver Response
            $response = new \APIResponse();
            $response->print();
		}

		/** @method getItemImages()
		 * Get All Images Associated with a Product
		 */
		public function findItemImages() {
			// Identify Product by Code or ID
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if ($product->error()) app_error("Error finding product: ".$product->error(),__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");
			$images = $product->images();
			if ($product->error()) app_error("Error getting images: ".$product->error(),__FILE__,__LINE__);
			$response = new \APIResponse();
			$response->addElement('images',$images);
			$response->print();
		}

		/** @method addItemMetadata()
		 * Add Metadata Key/Value Pair to a Product
		 */
		public function addItemMetadata() {
			$product = new \Product\Item();
			$product->get($_REQUEST['code']);
			if ($product->error()) $this->app_error("Error finding product: ".$product->error(),__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");

			$product->setMetadata($_REQUEST['key'],$_REQUEST['value']);
			if ($product->error()) $this->app_error("Error adding metadata: ".$product->error(),__FILE__,__LINE__);

            $response = new \APIResponse();
			$response->addElement('metadata',array(
				'key' => $_REQUEST['key'],
				'value' => $_REQUEST['value']
			));
            $response->print();
		}

		/** @method getItemMetadata()
		 * Get Metadata Value for a Product with associated key
		 */
		public function getItemMetadata() {
			$product = new \Product\Item();
			$product->get($_REQUEST['code']);
			if ($product->error()) app_log("Error finding product: ".$product->error(),'error',__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");

			$value = $product->getMetadata($_REQUEST['key']);
			if ($product->error()) app_error("Error getting metadata: ".$product->error(),__FILE__,__LINE__);

            $response = new \APIResponse();
			$response->addElement('value',$value);
            $response->print();
		}

		/** @method getAllItemMetadata()
		 * Get All Metadata Key/Value Pairs for a Product
		 */
		public function getAllItemMetadata() {
			$product = new \Product\Item();
			$product->get($_REQUEST['code']);
			if ($product->error()) app_error("Error finding product: ".$product->error(),__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");

			$metadata = $product->getAllMetadata();
			if ($product->error()) app_error("Error getting metadata: ".$product->error(),__FILE__,__LINE__);

            $response = new \APIResponse();
			$response->addElement('metadata',$metadata);
            $response->print();
		}

		/** @method dropItemMetadata()
		 * Remove Metadata Key/Value Pair from a Product
		 */
		public function dropItemMetadata() {
			$product = new \Product\Item();
			$product->get($_REQUEST['code']);
			if ($product->error()) app_error("Error finding product: ".$product->error(),__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");

			$product->dropMeta($_REQUEST['key']);
			if ($product->error()) app_error("Error dropping metadata: ".$product->error(),__FILE__,__LINE__);

            $response = new \APIResponse();
            $response->print();
		}

		/** @method addProductMeta()
		 * DEPRECATED
		 * Alias for addItemMetadata()
		 */
		public function addProductMeta() {
			$this->addItemMetadata();
		}

		/** @method _methods()
		 * Get a list of all available API methods
		 */
		public function _methods() {
			$validationClass = new \Product\Item();
			return array(
				'ping'			=> array(),
				'findItems'	=> array(
					'description'		=> 'Find products matching criteria',
					'authentication_required' => false,
					'return_type'		=> 'array',
					'parameters'		=> array(
						'code'		=> array(),
						'name'		=> array(),
						'status'	=> array(),
						'type'		=> array(),
						'variant_type'	=> array(
							'description' => 'Variant Type',
							'options' => array_merge(array(''), $validationClass->variantTypes())
						),
					)
				),
				'getItem'	=> array(
					'description'		=> 'Get a product by code or id',
					'authentication_required' => false,
					'return_type'		=> 'Product::Item',
					'return_element'	=> 'item',
					'parameters'		=> array(
						'id'		=> array(
							'requirement_group'	=> 1,
							'description'	=> 'Product ID',
							'hidden'		=> true,
							'content_type'	=> 'integer'
						),
						'code'		=> array(
							'requirement_group'	=> 2,
							'description'	=> 'Product Item Code or Sku',
							'validation_method'	=> 'Product::Item::validCode()'
						),
					)
				),
				'addItem'	=> array(
					'description'		=> 'Add a new product',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required'	=> 'manage products',
					'parameters'		=> array(
						'code'			=> array(
							'required' => true,
							'description' => 'Unique Product Item Code or Sku',
							'validation_method' => 'Product::Item::validCode()',
						),
						'name'			=> array(
							'required' => true,
							'description' => 'Product Name',
							'validation_method'	=> 'Product::Item::validName()'
						),
						'type'			=> array(
							'required' => true,
							'description' => 'Product Type',
							'options' => $validationClass->types()
						),
						'description'	=> array(
							'description' => 'Product Description',
							'validation_method'	=> 'Product::Item::safeString()'
						),
						'status'		=> array(
							'default' => 'ACTIVE',
							'options' =>$validationClass->statuses()
						),
					),
				),
				'changeInstanceCode'	=> array(
					'description' => 'Change the serial number of a product instance',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required'	=> 'manage products',
					'parameters' => array(
						'code'		=> array(
							'required' => true,
							'description' => 'Current serial number',
							'validation_method' => 'Product::Instance::validCode()'
						),
						'new_code'	=> array(
							'required' => true,
							'description' => 'New serial number',
							'validation_method' => 'Product::Instance::validCode()'
						),
						'product_code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'reason'	=> array(
							'required' => true,
							'description' => 'Reason for change',
							'validation_method' => 'Product::Instance::safeString()'
						),
					),
					'return_type' => 'bool'
				),
				'updateItem'	=> array(
					'description'	=> 'Update an existing product',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required'	=> 'manage products',
					'parameters'	=> array(
						'code'		=> array(
							'required' => true,
							'description' => 'Product Item Code or Sku',
							'validation_method' => 'Product::Item::validCode()'
						),
						'name'		=> array(
							'description' => 'New Product Name',
							'validation_method'	=> 'Product::Item::validName()'
						),
						'status'	=> array(
							'description' => 'New Product Status',
							'options' => $validationClass->statuses()
						),
						'type'		=> array(
							'description' => 'New Product Type',
							'options' => $validationClass->types()
						),
					)
				),
				'addItemMetadata'	=> array(
					'description'		=> 'Add Metadata Key/Value Pair to a Product',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required' => 'manage products',
					'parameters'		=> array(
						'code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'key'			=> array(
							'required' => true,
							'description' => 'Metadata Key',
							'validation_method' => 'Product::Item::validMetadataKey()'
						),
						'value'		=> array(
							'required' => true,
							'description' => 'Metadata Value',
							'validation_method' => 'Product::Item::validMetadataValue()'
						),
					)
				),
				'getItemMetadata' => array(
					'description'		=> 'Get Metadata Value for a Product with associated key',
					'authentication_required' => true,
					'parameters'		=> array(
						'code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'key'			=> array(
							'required' => true,
							'description' => 'Metadata Key',
							'validation_method' => 'Product::Item::validMetadataKey()'
						),
					)
				),
				'dropItemMetadata' => array(
					'description'		=> 'Remove Metadata Key/Value Pair from a Product',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required' => 'manage products',
					'parameters'		=> array(
						'code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'key'			=> array(
							'required' => true,
							'description' => 'Metadata Key',
							'validation_method' => 'Product::Item::validMetadataKey()'
						),
					)
				),
				'getAllItemMetadata' => array(
					'description'		=> 'Get All Metadata Key/Value Pairs for a Product',
					'authentication_required' => true,
					'parameters'		=> array(
						'code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
					)
				),
				'getVisibility' => array(
					'description'		=> 'Get the visibility settings for a product in a given realm',
					'authentication_required' => false,
					'parameters'		=> array(
						'code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'realm'	=> array(
							'required' => true,
							'description' => 'Visibility Realm',
							'options' => [
								'MARKETING',
								'SALES',
								'SUPPORT',
								'ADMINISTRATION'
							]
						)
					)
				),
				'setVisibility' => array(
					'description'		=> 'Set the visibility settings for a product in a given realm',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required' => 'manage products',
					'parameters'		=> array(
						'code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'realm'	=> array(
							'required' => true,
							'description' => 'Visibility Realm',
							'options' => [
								'MARKETING',
								'SALES',
								'SUPPORT',
								'ADMINISTRATION'
							]
						),
						'visibility'	=> array(
							'required' => true,
							'description' => 'Visibility Setting',
							'content-type' => 'boolean',
							'options' => ['true','false']
						)
					)
				),
				'addItemImage'	=> array(
					'description'	=> 'Assign an Existing Image to an Existing Product',
					'authentication_required'	=> true,
					'token_required' => true,
					'privilege_required' => 'manage products',
					'parameters' => array(
						'product_code' => array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'image_code' => array(
							'required' => true,
							'description' => 'Image Code',
							'validation_method' => 'Media::Image::validCode()'
						)
					)
				),
				'dropItemImage'	=> array(
					'description'	=> 'Remove an Image from a Product',
					'authentication_required'	=> true,
					'token_required' => true,
					'privilege_required' => 'manage products',
					'parameters' => array(
						'product_code' => array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'image_code' => array(
							'required' => true,
							'description' => 'Image Code',
							'validation_method' => 'Media::Image::validCode()'
						)
					)
				),
				'findItemImages' => array(
					'description'	=> 'Find Images assigned to a Product',
					'authentication_required' => false,
					'parameters' => array(
						'product_code' => array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
					)
				),
				'addPrice'      => array(
					'description'		=> 'Add a price to a product',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required' => 'manage products',
					'parameters'	=> array(
	                    'product_code'  => array(
							'required' => true,
							'description' => 'Unique Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
	                    'amount'        => array(
							'required' => true,
							'description' => 'Price Amount',
							'content_type' => 'decimal'
						),
	                    'date_active'   => array(
							'required' => false,
							'default' => get_mysql_date(time()),
							'validation_method'	=> 'Product::Item::validDate()',
						),
	                    'status'        => array(
							'description' => 'Price Status',
							'options' => $validationClass->statuses(),
							'validation_method' => 'Product::Item::validStatus()',
							'default' => 'ACTIVE',
							'options' => $validationClass->statuses()
						),
					)
                ),
				'getPrice'		=> array(
					'description'		=> 'Get the price of a product',
					'authentication_required' => false,
					'parameters'	=> array(
						'product_code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
					)
				),
				'findRelationships'	=> array(
					'description'		=> 'Find relationships between products',
					'authentication_required' => false,
					'parameters'		=> array(
						'parent_code'	=> array(
							'description' => 'Parent Product Code',
							'required' => true,
							'validation_method' => 'Product::Item::validCode()'
						),
						'child_code'	=> array(
							'description' => 'Child Product Code',
							'required' => true,
							'validation_method' => 'Product::Item::validCode()'
						),
					)
				),
				'addRelationship'	=> array(
					'description'		=> 'Add a relationship between products',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required' => 'manage products',
					'parameters'		=> array(
						'parent_code'	=> array(
							'required' => true,
							'description' => 'Parent Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'child_code'	=> array(
							'required' => true,
							'description' => 'Child Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
					)
				),
				'getRelationship'	=> array(
					'description'		=> 'Get a relationship between products',
					'authentication_required' => false,
					'parameters'		=> array(
						'parent_code'	=> array(
							'description' => 'Parent Product Code',
							'required' => true,
							'validation_method' => 'Product::Item::validCode()'
						),
						'child_code'	=> array(
							'description' => 'Child Product Code',
							'required' => true,
							'validation_method' => 'Product::Item::validCode()'
						),
					)
				),
				'findGroupItems'	=> array(
					'description'		=> 'Find items in a product group',
					'authentication_required' => false,
					'parameters'		=> array(
						'code'	=> array(
							'required' => true,
							'description' => 'Product Group Code',
							'validation_method' => 'Product::Item::validCode()'
						),
					)
				),
				'getInstance'	=> array(
					'description'		=> 'Get a product instance by code or id',
					'authentication_required' => true,
					'token_required'	=> true,
					'parameters'	=> array(
						'id'		=> array(
							'requirement_group'	=> 1,
							'description'	=> 'Product Instance ID',
							'hidden'		=> true,
							'content_type'	=> 'integer'
						),
						'code'		=> array(
							'requirement_group'	=> 2,
							'description'	=> 'Product Instance Code or Serial Number',
							'validation_method'	=> 'Product::Instance::validCode()'
						),
						'product_id'	=> array(
							'description'	=> 'Product ID',
							'content_type'	=> 'integer'
						),
					)
				),
				'addInstance'	=> array(
					'description'		=> 'Add a new product instance',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required'	=> 'manage product instances',
					'parameters'		=> array(
						'code'			=> array(
							'required' => true,
							'description' => 'Product Instance Code or Serial Number',
							'validation_method' => 'Product::Instance::validCode()'
						),
						'product_code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'organization_id'	=> array(
							'requirement_group'	=> 1,
							'description' => 'Organization ID',
							'content_type'	=> 'integer',
							'hidden'		=> true
						),
						'organization_code'	=> array(
							'requirement_group' => 2,
							'description' => 'Organization Code',
							'validation_method' => 'Register::Organization::validCode()'
						),
						'name'			=> array(
							'description' => 'Product Instance Name',
							'validation_method'	=> 'Product::Instance::safeString()'
						),
					)
				),
				'updateInstance'	=> array(
					'description'		=> 'Update an existing product instance',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required'	=> 'manage product instances',
					'parameters'	=> array(
						'code'		=> array(
							'required' => true,
							'description' => 'Product Instance Code or Serial Number',
							'validation_method' => 'Product::Instance::validCode()'
						),
						'product_code'	=> array(
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'name'		=> array(
							'description' => 'Product Instance Name',
							'validation_method'	=> 'Product::Instance::validName()'
						),
						'organization'	=> array(
							'description' => 'Organization Code',
							'validation_method' => 'Register::Organization::validCode()'
						),
						'new_product_code'	=> array(
							'description' => 'New Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
					)
				),
				'findInstances'	=> array(
					'description'		=> 'Find product instances matching criteria',
					'authentication_required' => true,
					'parameters'		=> array(
						'code'		=> array(
							'description'	=> 'Product Instance Code or Serial Number',
							'validation_method' => 'Product::Instance::validCode()'
						),
						'name'		=> array(
							'description'	=> 'Product Instance Name',
							'validation_method'	=> 'Product::Instance::validName()'
						),
						'product_code'	=> array(
							'description'	=> 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'organization_code'	=> array(
							'description'	=> 'Organization Code',
							'validation_method' => 'Register::Organization::validCode()'
						),
					)
				),
				'addProductImage'	=> array(
					'description'		=> 'Add an image to a product',
					'authentication_required' => true,
					'token_required'	=> true,
					'privilege_required'	=> 'manage products',
					'parameters'		=> array(
						'product_code'	=> array(
							'required' => true,
							'description' => 'Product Code',
							'validation_method' => 'Product::Item::validCode()'
						),
						'image_code'	=> array(
							'required' => true,
							'description' => 'Image Code',
							'validation_method' => 'Media::Item::validCode()'
						),
						'label'		=> array(
							'required' => false,
							'description' => 'Optional label for the image'
						),
					)
				),
			);
		}
	}
