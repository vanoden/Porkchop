<?php
	namespace Product;

	class Item Extends \BaseModel {

		public $code;
		public $name;
		public $description;
		public $type;
		public $status;

		/**
		 * Constructor
		 * @param int $id - optional 
		 * @return void 
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'product_products';
            $this->_addStatus(array('ACTIVE','HIDDEN','DELETED'));
			$this->_auditEvents = true;
    		parent::__construct($id);
		}

		/**
		 * Get the root product group.  This is just a placeholder for the top
		 * level of the product hierarchy.  Returns the id of the default
		 * product group for adding new products if a parent group isn't provided.
		 * @return bool 
		 */
		public function defaultCategory() {
			$get_category_query = "
				SELECT	id
				FROM	product_products
				WHERE	code = '_root'
			";
			$rs = $GLOBALS['_database']->Execute($get_category_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details($this->id);
		}

		/**
		 * Special product update function just to change the code of a product.
		 * The regular update method should not allow this as special privileges
		 * and auditing are required.
		 * @param mixed $new_code 
		 * @param mixed $reason 
		 * @return bool 
		 */
		public function changeCode($new_code, $reason): bool {
			$this->clearError();
			$database = new \Database\Service();

			app_log("Changing product code from ".$this->code." to ".$new_code,'notice',__FILE__,__LINE__);

			// Check the users authorization - Should really be done in the interface
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return false;
			}

			// Validate the new code
			if (! $this->validCode($new_code)) {
				$this->error("Invalid code");
				return false;
			}

			// Bust the existing cache
			$cache_key = "product[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			// Prepare the query
			$update_product_query = "
				UPDATE	product_products
				SET		code = ?
				WHERE	id = ?";

			// Add Parameters and Execute Query
			$database->AddParam($new_code);
			$database->AddParam($this->id);
			$database->Execute($update_product_query);

			// Check for errors
			if ($database->ErrorMsg()) {
				$this->error($database->ErrorMsg());
				return false;
			}
			return true;
		}

		/**
		 * Update the product with the provided parameters.  This is the standard
		 * update method for the product class.
		 * @param mixed $parameters 
		 * @return bool 
		 */
		public function update($parameters = []): bool {

			$this->clearError();
			$database = new \Database\Service();

			app_log("Product::Item::update()",'trace',__FILE__,__LINE__);
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return false;
			}

			# Bust Cache
			$cache_key = "product[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);
			$cache_item->delete();

			$ok_params = array(
				"code"			=> '/^\w[\w\-\.\_]*$/',
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

			$bind_params = array();

			# Loop Through Parameters
			foreach (array_keys($parameters) as $parameter) {
				if ($ok_params[$parameter]) {
					$update_product_query .= ",
					$parameter	= ?";
					$database->AddParam($parameters[$parameter]);
				}
			}

			$update_product_query .= "
				WHERE	id = ?
			";
			$database->AddParam($this->id);
			$rs = $database->Execute($update_product_query);
            if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				app_log($update_product_query,'debug');
				return null;
            }

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));	
					
			return $this->details();
		}

		/**
		 * Add a new product with the provided parameters.
		 * @param mixed $parameters 
		 * @return bool 
		 */
		public function add($parameters = []) {
			app_log("Product::Item::add()",'trace');
			$this->clearError();
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->login." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return false;
			}

			# Make Sure Minimum Parameters Sent
			if (! $parameters['code']) {
				$this->error("Code required to add product");
				return null;
			}
			if ($this->get($parameters['code'])) {
				$this->error("Code '".$parameters['code']."' is not unique");
				return null;
			}
			else {
				# Hide error because no match found above
				$this->clearError();
			}

			if (! $parameters['type']) {
				$this->error("Valid product type required");
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
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return null;
            }
			$this->id = $GLOBALS['_database']->Insert_ID();

			// add audit log
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			app_log("Created Product ".$this->id,'notice');
			return $this->update($parameters);
		}

		/**
		 * Get all database details for the product.
		 * Acquire from cache first if available.  Otherwise populate cache for
		 * next access.
		 * @return bool - True if product found
		 */
		public function details(): bool {
			app_log("Product::Item::details()",'trace');
			$this->clearError();
			$database = new \Database\Service();

			$cache_key = "product[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);

			if (($this->id) and ($product = $cache_item->get())) {
				// Object found in cache, populate properties from cache
				$product->_cached = true;
				$this->id = $product->id;
				$this->name = $product->name;
				$this->code = $product->code;
				$this->status = $product->status;
				$this->type = $product->type;
				$this->description = $product->description;
				$this->cached($product->_cached);
				$this->exists(true);

				# In Case Cache Corrupted
				if ($product->id) {
					app_log("Product '".$this->name."' [".$this->id."] found in cache",'trace');
					return true;
				}
				else {
					$this->error("Product ".$this->id." returned unpopulated cache");
				}
			}
			else {
				$this->cached(false);
			}

			// Prepare Query to Get Product Details
			$get_details_query = "
				SELECT	id,
						code,
						status,
						type,
						name,
						description
				FROM	product_products
				WHERE	id = ?";

			// Add params and execute query
			$database->AddParam($this->id);
			$rs = $database->Execute($get_details_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Grab the object and populate properties
			$object = $rs->FetchNextObject(false);

			$this->id = $object->id;
			$this->code = $object->code;
			$this->name = $object->name;
			$this->status = $object->status;
			$this->description = $object->description;
			$this->type = $object->type;
			$this->exists(true);

			// Cache Product Object
			app_log("Setting cache key ".$cache_key);
			if ($object->id)
				if ($cache_item->set($object))
					app_log("Cache result: success");
				else
					app_log("Cache result: failed: ".$cache_item->error());

			return true;
		}

		public function getByCode($code) {
			$get_details_query = "
				SELECT	id
				FROM	product_products
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($code));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($this->id) = $rs->FetchRow();

			if ($this->id)
				return $this->details();
			else
				return false;
		}

		public function inCategory($category_id) {
			app_log("Product::Item::inCategory()",'trace',__FILE__,__LINE__);

			# Get Parent ID
			$parent = new \Product\Item($category_id);
			if (! $parent->id) {
				$this->error("Could not find category $category_id");
				return false;
			}

			# Prepare Query to Tie Product to Category
			$in_category_query = "
				SELECT	1
				FROM	product_relations
				WHERE	product_id = ?
				AND		parent_id = ?
			";
			$bind_params = array($this->id,$parent->id);
			$rs = $GLOBALS['_database']->Execute($in_category_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($found) = $rs->FetchRow();
			if (! $found) $found = 0;
			return $found;
		}

		public function addToCategory($category_id) {
			# Get Parent ID
			$category = new \Product\Item($category_id);
			if (! $category->id) {
				$this->error("Could not find category $category_id");
				return false;
			}

			# Prepare Query to Tie Product to Category
			$to_category_query = "
				INSERT
				INTO	product_relations
				(		product_id,
						parent_id
				)
				VALUES
				(		?,?
				)
			";
			$bind_params = array($this->id,$category->id);
			$rs = $GLOBALS['_database']->Execute($to_category_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			# Load MediaItem Class
			$images = array();
			while (list($image_id) = $rs->FetchRow()) {
				$image = new \Media\Item($image_id);
				if ($image->error()) {
					$this->error("Could not load Media Item class: ".$image->error());
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($value) = $rs->FetchRow();
            if (!empty($value)) {
    			return (object) array(
				    'product_id'	=> $this->id,
				    'key'			=> $key,
				    'value'			=> $value
			    );
            }
            else return null;
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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			return 1;
		}

		public function metadata() {
			$meta = new \Product\Item\Metadata();
			$meta->fk_id = $this->id;
			return $meta;
		}

		public function currentPrice() {
			$priceList = new \Product\PriceList();
			$prices = $priceList->find(array('product_id' => $this->id, 'status' => 'ACTIVE'));
			if ($priceList->error()) {
				$this->error($priceList->error());
				return null;
			}
			elseif (empty($prices)) {
				return 0;
			}
			else {
    			return array_pop($prices);
			}
		}

		public function prices() {
			$priceList = new \Product\PriceList();
			$prices = $priceList->find(array('product_id' => $this->id));
			if ($priceList->error()) {
				$this->error($priceList->error());
				return null;
			} else {
				return $prices;
			}
		}

		public function addPrice($parameters = array()) {
			if (! $GLOBALS['_SESSION_']->customer->can('edit product prices')) $this->error("Permission denied");
			$price = new \Product\Price();
			$parameters = array(
				'product_id'	=> $this->id,
				'amount'		=> $parameters['amount'],
				'date_active'	=> $parameters['date_active'],
				'status'		=> $parameters['status']
			);
			if ($price->add($parameters)) return $price;
			$this->error("Error adding price: ".$price->error());			
			return false;
		}

		public function getPrice($parameters = array()) {
			$price = new \Product\Price();
			if ($price->getCurrent($this->id)) return $price;
			$this->error($price->error());
			return null;
		}

        public function getPriceAmount($parameters = array()) {
			$price = new \Product\Price();
			return $price->getCurrent($this->id);
        }

        public function validCode($string): bool {
            if (preg_match('/^\w[\w\-\.\_\s]*$/',$string)) return true;
            else return false;
        }

		public function validName($string): bool {
			if (preg_match('/^[\w\-\.\_\s\:\!]+$/', $string))
				return true;
			else
				return false;
		}	

        public function validType($string): bool {
            if (in_array($string,array('group','kit','inventory','unique','service'))) return true;
            else return false;
        }

        public function validStatus($string): bool {
            if (in_array($string,array('ACTIVE','HIDDEN','DELETED'))) return true;
            else return false;
        }

		public function isMultiZone() {
			if (preg_match("/(SF|PM|MB)400\-/",$this->code)) return true;
			else return false;
		}
	}
