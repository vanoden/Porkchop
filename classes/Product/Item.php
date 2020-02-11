<?php
	namespace Product;

	class Item {
		public $id;
		public $code;
		public $name;
		public $description;
		public $type;
		private $_flat = false;
		public $_cached = 0;

		public function __construct($id = 0,$flat = false) {
			if ($flat) $this->_flat = true;
			# Clear Error Info
			$this->_error = '';

			# Database Initialization
			$schema = new Schema();
			if ($schema->error) {
				$this->_error = $schema->error;
				return null;
			}

			if ($id) {
				$this->id = $id;
				$this->details();
			}
		}

		public function defaultCategory() {
			$get_category_query = "
				SELECT	id
				FROM	product_products
				WHERE	code = '_root'
			";
			$rs = $GLOBALS['_database']->Execute($get_category_query);
			if (! $rs) {
				$this->_error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details($this->id);
		}

		public function get($code = '') {
			app_log("Product::Item::get()",'trace',__FILE__,__LINE__);
			# Prepare Query to Get Product
			$get_object_query = "
				SELECT	id
				FROM	product_products
				WHERE	code = ?
			";

			# Return new product
            $rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
            if (! $rs) {
                $this->_error = $GLOBALS['_database']->ErrorMsg();
                return null;
            }
			else {
				list($this->id) = $rs->FetchRow();
				if (! $this->id) {
					$this->_error = "No Product Found";
					return null;
				}
				return $this->details();
			}
		}

		public function update($parameters) {
			app_log("Product::Item::update()",'trace',__FILE__,__LINE__);
			if (! $GLOBALS['_SESSION_']->customer->has_role('product manager')) {
				$this->_error = "You do not have permissions for this task.";
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_']->customer,true),'debug',__FILE__,__LINE__);
				return null;
			}

			# Bust Cache
			$cache_key = "product[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cacke_key);
			$cache_item->delete();

			$ok_params = array(
				"status"		=> '/^\w+$/',
				"type"			=> "/.+/",
				"name"			=> '/^[\w\-\.\_\s]+$/',
				"description"	=> "/.+/",
			);

			# Prepare Query to Update Product
			$update_product_query = "
				UPDATE	product_products
				SET		id = id
			";

			# Loop Through Parameters
			foreach (array_keys($parameters) as $parameter) {
				if ($ok_params[$parameter]) {
					$value = $GLOBALS['_database']->qstr($parameters[$parameter],get_magic_quotes_gpc);
					$update_product_query .= ",
					$parameter	= $value";
				}
			}

			$update_product_query .= "
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$update_product_query,
				array($this->id)
			);
            if (! $rs) {
				$this->_error = $GLOBALS['_database']->ErrorMsg();
				app_log($update_product_query,'debug',__FILE__,__LINE__);
				return null;
            }
			return $this->details();
		}

		public function add($parameters) {
			app_log("Product::Item::add()",'trace',__FILE__,__LINE__);
			if (! $GLOBALS['_SESSION_']->customer->has_role('product manager')) {
				$this->_error = "You do not have permissions for this task.";
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return null;
			}
			$this->_error = '';

			# Make Sure Minimum Parameters Sent
			if (! $parameters['code']) {
				$this->_error = "Code required to add product";
				return null;
			}
			if ($this->get($parameters['code'])) {
				$this->_error = "Code '".$parameters['code']."' is not unique";
				return null;
			}
			else {
				# Hide error because no match found above
				$this->_error = null;
			}

			if (! $parameters['type']) {
				$this->_error = "Valid product type required";
				return null;
			}

			# Prepare Query to Create Product
			$add_product_query = "
				INSERT
				INTO	product_products
				(		code,
						type,
						status
				)
				VALUES
				(		?,?,?)
			";

			# Return new product
            $rs = $GLOBALS['_database']->Execute(
				$add_product_query,
				array(
					$parameters["code"],
					$parameters["type"],
					$parameters['status']
				)
			);
            if (! $rs) {
                $this->_error = "SQL Error: ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }
			$this->id = $GLOBALS['_database']->Insert_ID();

			app_log("Created Product ".$this->id,'notice');
			return $this->update($parameters);
		}

		public function details() {
			app_log("Product::Item::details()",'trace');

			$cache_key = "product[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);

			# Cached Organization Object, Yay!
			if (($this->id) and ($product = $cache_item->get())) {
				$product->_cached = 1;
				$this->id = $product->id;
				$this->name = $product->name;
				$this->code = $product->code;
				$this->status = $product->status;
				$this->type = $product->type;
				$this->description = $product->description;
				$this->_cached = $product->_cached;
				if (! $this->_flat) {
					$this->metadata = $this->getMeta();
					$this->images = $this->images();
				}

				# In Case Cache Corrupted
				if ($product->id) {
					app_log("Product '".$this->name."' [".$this->id."] found in cache",'trace');
					return $product;
				}
				else {
					$this->_error = "Product ".$this->id." returned unpopulated cache";
				}
			}
			else {
				$this->_cached = 0;
			}

			# Prepare Query to Get Product Details
			$get_details_query = "
				SELECT	id,
						code,
						status,
						type,
						name,
						description
				FROM	product_products
				WHERE	id = ?";

			$rs = $GLOBALS['_database']->Execute(
				$get_details_query,
				array($this->id)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Product::Item::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$object = $rs->FetchNextObject(false);

			$this->id = $object->id;
			$this->code = $object->code;
			$this->name = $object->name;
			$this->description = $object->description;
			$this->type = $object->type;

			# Cache Product Object
			app_log("Setting cache key ".$cache_key);
			if ($object->id)
				if ($cache_item->set($object))
					app_log("Cache result: success");
				else
					app_log("Cache result: failed: ".$cache_item->error);

			$this->metadata = $this->getMeta();
			$this->images = $this->images();

			return $object;
		}

		public function inCategory($category,$product = '') {
			app_log("Product::Item::inCategory()",'trace',__FILE__,__LINE__);
			if ($product) {
				$_product = new Product($product);
				$product_id = $_product->{id};
			}
			else
				$product_id = $this->id;
			if (! $product_id)
			{
				$this->_error = "Could not find product";
				return 0;
			}

			# Get Parent ID
			$_parent = new Product($category);
			$parent_id = $_parent->id;
			if (! $parent_id)
			{
				$this->_error = "Could not find category $category";
				return 0;
			}

			# Prepare Query to Tie Product to Category
			$in_category_query = "
				SELECT	1
				FROM	product_relations
				WHERE	product_id = '".$product_id."'
				AND		parent_id = '".$parent_id."'
			";
			#error_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$in_category_query)));
			$rs = $GLOBALS['_database']->Execute($in_category_query);
			if (! $rs)
			{
				$this->_error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($found) = $rs->FetchRow();
			if (! $found) $found = 0;
			return $found;
		}

		public function addToCategory($category,$product = '') {
			if ($product)
			{
				$_product = new Product($product);
				$product_id = $_product->{id};
			}
			else
				$product_id = $this->id;
			if (! $product_id)
			{
				$this->_error = "Could not find product";
				return 0;
			}

			# Get Parent ID
			$_parent = new Product($category);
			$parent_id = $_parent->id;
			if (! $parent_id)
			{
				$this->_error = "Could not find category $category";
				return 0;
			}

			# Prepare Query to Tie Product to Category
			$to_category_query = "
				INSERT
				INTO	product_relations
				(		product_id,
						parent_id
				)
				VALUES
				(		'$product_id',
						'$parent_id'
				)
			";
			#error_log(preg_replace("/(\n|\r)/","",preg_replace("/\t/"," ",$to_category_query)));
			$rs = $GLOBALS['_database']->Execute($to_category_query);
			if ($GLOBALS['_database']->ErrorMsg())
			{
				$this->_error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			return 1;
		}

		public function images() {
			# Get Images From Database
			$get_image_query = "
				SELECT	image_id
				FROM	product_images
				WHERE	product_id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_image_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Product::getImages: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			# Load MediaItem Class
			$images = array();
			while (list($image_id) = $rs->FetchRow()) {
				$image = new \Media\Item($image_id);
				if ($image->error) {
					$this->_error = "Could not load Media Item class: ".$_image->error;
					return null;
				}
				array_push($images,$image);
			}
			return $images;
		}
		public function addImage($image_id) {
			# Prepare Query to Tie Product to Category
			$add_image_query = "
				INSERT
				INTO	product_images
				(		product_id,
						image_id
				)
				VALUES
				(		?,?
				)
			";
			$GLOBALS['_database']->Execute($add_image_query,array($this->id,$image_id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Product::addImage: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			return 1;
		}
		public function dropImage($image_id) {
			# Prepare Query to Drop Image from Product
			$drop_image_query = "
				DELETE
				FROM	product_images
				WHERE	product_id = ?
				AND		image_id = ?
			";
			$GLOBALS['_database']->Execute($drop_image_query,array($this->id,$image_id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Product::dropImage: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			return 1;
		}
		public function hasImage($image_id) {
			# Prepare Query to Get Image
			$get_image_query = "
				SELECT	1
				FROM	product_images
				WHERE	product_id = ?
				AND		image_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_image_query,array($this->id,$image_id));
			if (! $rs) {
				$this->_error = "SQL Error in Product::hasImage: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($found) = $rs->FetchRow();
			if (! $found) $found = 0;
			return $found;
		}
		public function getMeta() {
			$get_meta_query = "
				SELECT	`key`,value
				FROM	product_metadata
				WHERE	product_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_meta_query,
				array($this->id)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Product::getMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadata = array();
			while (list($label,$value) = $rs->FetchRow()) {
				$metadata[$label] = $value;
			}
			return $metadata;
		}
		public function getMetadata($key) {
			$get_meta_query = "
				SELECT	value
				FROM	product_metadata
				WHERE	product_id = ?
				AND		`key` = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_meta_query,
				array($this->id,$key)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Product::Item::getMetadata: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($value) = $rs->FetchRow();
			return (object) array(
				'product_id'	=> $this->id,
				'key'			=> $key,
				'value'			=> $value
			);
		}
		public function addMeta($key,$value) {
			$add_meta_query = "
				REPLACE
				INTO	product_metadata
				(		product_id,`key`,value)
				VALUES
				(		?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_meta_query,
				array(
					$this->id,$key,$value
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Product::addMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		public function error() {
			return $this->_error;
		}
	}
?>
