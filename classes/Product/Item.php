<?php
	namespace Product;

	class Item {
		public $id;
		public $code;
		public $name;
		public $description;

		public function __construct($id = 0) {
			# Clear Error Info
			$this->error = '';

			# Database Initialization
			$schema = new Schema();
			if ($schema->error) {
				$this->error = $schema->error;
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
				$this->error = $GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details($this->id);
		}

		public function get($code = '') {
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
                $this->error = $GLOBALS['_database']->ErrorMsg();
                return 0;
            }
			else {
				list($this->id) = $rs->FetchRow();
				if (! $this->id) {
					$this->error = "No Product Found";
					return null;
				}
				return $this->details();
			}
		}

		public function update($parameters) {
			if (! $GLOBALS['_SESSION_']->customer->has_role('product manager')) {
				$this->error = "You do not have permissions for this task.";
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_']->customer,true),'debug',__FILE__,__LINE__);
				return null;
			}

			$ok_params = array(
				"status"		=> "/.+/",
				"type"			=> "/.+/",
			);

			# Prepare Query to Update Product
			$update_product_query = "
				UPDATE	product_products
				SET		id = id
			";

			# Loop Through Parameters
			foreach (array_keys($parameters) as $parameter) {
				if ($ok_params[$parameter]) {
					$value = $GLOBALS['_database']->qstr($parameters[$parameter],get_magic_quotes_gpc());
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
				$this->error = $GLOBALS['_database']->ErrorMsg();
				app_log($update_product_query,'debug',__FILE__,__LINE__);
				return null;
            }
			return $this->details();
		}

		public function add($parameters) {
			if (! $GLOBALS['_SESSION_']->customer->has_role('product manager')) {
				$this->error = "You do not have permissions for this task.";
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return 0;
			}
			$this->error = '';

			# Make Sure Minimum Parameters Sent
			if (! $parameters['code']) {
				$this->error = "Code required to add product";
				return 0;
			}
			if ($this->get($parameters['code'])) {
				$this->error = "Code '".$parameters['code']."' is not unique";
				return 0;
			}

			if (! $parameters['type']) {
				$this->error = "Valid product type required";
				return 0;
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
                $this->error = "SQL Error: ".$GLOBALS['_database']->ErrorMsg();
                return 0;
            }
			$this->id = $GLOBALS['_database']->Insert_ID();

			app_log("Created Product ".$this->id,'notice',__FILE__,__LINE__);
			return $this->details();
		}

		public function details() {
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
				$this->error = "SQL Error in Product::Item::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$object = $rs->FetchNextObject(false);

			$this->id = $object->id;
			$this->code = $object->code;
			$this->name = $object->name;
			$this->description = $object->description;
			$this->metadata = $this->getMeta();
			$this->images = $this->images();

			return $object;
		}

		public function inCategory($category,$product = '') {
			if ($product)
			{
				$_product = new Product($product);
				$product_id = $_product->{id};
			}
			else
				$product_id = $this->id;
			if (! $product_id)
			{
				$this->error = "Could not find product";
				return 0;
			}

			# Get Parent ID
			$_parent = new Product($category);
			$parent_id = $_parent->id;
			if (! $parent_id)
			{
				$this->error = "Could not find category $category";
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
				$this->error = $GLOBALS['_database']->ErrorMsg();
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
				$this->error = "Could not find product";
				return 0;
			}

			# Get Parent ID
			$_parent = new Product($category);
			$parent_id = $_parent->id;
			if (! $parent_id)
			{
				$this->error = "Could not find category $category";
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
				$this->error = $GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Product::getImages: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}

			# Load MediaItem Class
			$images = array();
			while (list($image_id) = $rs->FetchRow()) {
				$_image = new \MediaItem();
				if ($_image->error) {
					$this->error = "Could not load MediaItem class: ".$_image->error;
					return null;
				}
				$image = $_image->details($image_id);
				if ($_image->error) {
					$this->error = "Error loading image: ".$_image->error;
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
				$this->error = "SQL Error in Product::addImage: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Product::dropImage: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Product::hasImage: ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in Product::getMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$metadata = array();
			while (list($label,$value) = $rs->FetchRow()) {
				$metadata[$label] = $value;
			}
			return $metadata;
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
				$this->error = "SQL Error in Product::addMeta: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
	}
?>
