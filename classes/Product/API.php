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
		### Add a Relationship							###
		###################################################
		public function addRelationship() {
			$_product = new \Product\Item();
			if (defined($_REQUEST['parent_code']))
			{
				$parent = $_product->get($_REQUEST['parent_code']);
				if (! $parent->id) $this->error("Parent product '".$_REQUEST['parent_code']."' not found");
				$_REQUEST['parent_id'] = $parent->id;
			}
			if ($_REQUEST['child_code'])
			{
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
			$_REQUEST['type'] = 'group';
			$this->addItem();
		}
	
		###################################################
		### Update a Group								###
		###################################################
		public function updateGroup() {
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
		### Add Image to Product						###
		###################################################
		public function addItemImage() {
			# Load Media Module
			require_once(MODULES."/media/_classes/default.php");
	
			$_product = new \Product\Item();
			$product = $_product->get($_REQUEST['product_code']);
			if ($_product->error) app_error("Error finding product: ".$_product->error,__FILE__,__LINE__);
			if (! $product->id) $this->error("Product not found");
	
			$_image = new \Media\Item();
			$image = $_image->get($_REQUEST['image_code']);
			if ($_image->error) app_error("Error finding image: ".$_image->error,__FILE__,__LINE__);
			if (! $image->id) $this->error("Image not found");
	
	
			$_product->addImage($product->id,$image->id,$_REQUEST['label']);
			if ($_product->error) app_error("Error adding image: ".$_product->error,__FILE__,__LINE__);
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
				)
			);
		}
	}
