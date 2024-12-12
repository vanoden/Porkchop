<?php
	namespace Product;

	/**
	 * Class Item
	 * 
	 * Represents a product item in the system.
	 * 
	 * @package Product
	 */
	class Item Extends \BaseModel {

		/** @var string The product code */
		public string $code = "";

		/** @var string The product name */
		public string $name = "";

		/** @var string The product description */
		public string $description = "";

		/** @var string The product type */
		public string $type = "";

		/** @var string The product status */
		public string $status = "ACTIVE";

		/**
		 * Constructor
		 * 
		 * @param int $id Optional product ID
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'product_products';
            $this->_addStatus(array('ACTIVE','HIDDEN','DELETED'));
			$this->_metaTableName = 'product_metadata';
			$this->_tableMetaFKColumn = 'product_id';
			$this->_tableMetaKeyColumn = 'key';
			$this->_auditEvents = true;
    		parent::__construct($id);
		}

		/**
		 * Get the root product group
		 * 
		 * @return bool True if successful, false otherwise
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
		 * Change the product code
		 * 
		 * @param string $new_code The new product code
		 * @param string $reason The reason for changing the code
		 * @return bool True if successful, false otherwise
		 */
		public function changeCode($new_code, $reason): bool {
			$this->clearError();
			$database = new \Database\Service();

			app_log("Changing product code from ".$this->code." to ".$new_code,'notice',__FILE__,__LINE__);

			// Check the users authorization - Should really be done in the interface
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->code." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
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
		 * Update the product with the provided parameters
		 * 
		 * @param array $parameters The parameters to update
		 * @return bool True if successful, false otherwise
		 */
		public function update($parameters = []): bool {

			$this->clearError();
			$database = new \Database\Service();

			app_log("Product::Item::update()",'trace',__FILE__,__LINE__);
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->code." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
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
		 * Add a new product
		 * 
		 * @param array $parameters The parameters for the new product
		 * @return bool|null True if successful, null if failed
		 */
		public function add($parameters = []) {
			app_log("Product::Item::add()",'trace');
			$this->clearError();
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->code." failed to update products because not product manager role",'notice',__FILE__,__LINE__);
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
		 * Get all database details for the product
		 * 
		 * @return bool True if product found, false otherwise
		 */
		public function details(): bool {
			app_log("Product::Item::details()",'trace');
			$this->clearError();
			$database = new \Database\Service();

			$cache_key = "product[".$this->id."]";
			$cache_item = new \Cache\Item($GLOBALS['_CACHE_'],$cache_key);

			if (($this->id) && ($product = $cache_item->get())) {
				// Object found in cache, populate properties from cache
				$product->_cached = true;
				$this->id = $product->id;
				if (empty($product->name)) $product->name = '';
				else $this->name = $product->name;
				$this->code = $product->code;
				$this->status = $product->status;
				$this->type = $product->type;
				if (empty($product->description)) $product->description = '';
				else $this->description = $product->description;
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
			if ($object->id) {
				$this->id = $object->id;
				$this->code = $object->code;
				$this->name = strval($object->name);
				$this->status = $object->status;
				$this->description = strval($object->description);
				$this->type = $object->type;
				$this->exists(true);
			}
			else {
				$this->exists(false);
				$this->id = 0;
				$this->name = "";
				$this->code = "";
				$this->description = "";
				return false;
			}

			// Cache Product Object
			app_log("Setting cache key ".$cache_key);
			if ($object->id)
				if ($cache_item->set($object))
					app_log("Cache result: success");
				else
					app_log("Cache result: failed: ".$cache_item->error());

			return true;
		}

		/**
		 * Get a product by its code
		 * 
		 * @param string $code The product code
		 * @return bool|null True if found, false if not found, null on error
		 */
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

		/**
		 * Check if the product is in a specific category
		 * 
		 * @param int $category_id The category ID
		 * @return int|null 1 if in category, 0 if not, null on error
		 */
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

		/**
		 * Add the product to a category
		 * 
		 * @param int $category_id The category ID
		 * @return bool True if successful, false otherwise
		 */
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

		/**
		 * Get all images associated with the product
		 * 
		 * @return array|null Array of Storage\File objects or null on error
		 */
		public function images() {
			$database = new \Database\Service();

			$get_images_query = "
				SELECT i.image_id, i.view_order, i.label
				FROM product_images i
				WHERE i.product_id = ?
				ORDER BY i.view_order ASC
			";

			$database->AddParam($this->id);
			$rs = $database->Execute($get_images_query);

			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$images = array();
			while ($row = $rs->FetchRow()) {
				$file = new \Storage\File($row['image_id']);
				if ($file->id) {
					$file->view_order = $row['view_order'];
					$file->label = $row['label'];
					$images[] = $file;
				}
			}

			return $images;
		}

		/**
		 * Add an image to the product
		 * 
		 * @param int $image_id The image ID to add
		 * @return int 1 if successful, 0 otherwise
		 */
		public function addImage($image_id) {

			// Prepare Query to Tie Product to Category
			$add_image_query = "
				INSERT
				INTO	product_images
				(		product_id,
						image_id,
						label
				)
				VALUES
				(?,?,?)
			";

			$GLOBALS['_database']->Execute($add_image_query,array($this->id,$image_id,'product image'));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return 0;
			}
			return 1;
		}

		/**
		 * Remove an image from the product
		 * 
		 * @param int $image_id The image ID to remove
		 * @return int 1 if successful, 0 otherwise
		 */
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

		/**
		 * Check if the product has a specific image
		 * 
		 * @param int $image_id The image ID to check
		 * @return int|null 1 if has image, 0 if not, null on error
		 */
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

		/**
		 * Get the current price of the product
		 * 
		 * @return \Product\Price|int|null Price object, 0 if no price, or null on error
		 */
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

		/**
		 * Get all prices for the product
		 * 
		 * @return array|null Array of prices or null on error
		 */
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

		/**
		 * Add a new price for the product
		 * 
		 * @param array $parameters The price parameters
		 * @return \Product\Price|false Price object if successful, false otherwise
		 */
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

		/**
		 * Get the current price of the product
		 * 
		 * @param array $parameters Optional parameters
		 * @return \Product\Price|null Price object or null on error
		 */
		public function getPrice($parameters = array()) {
			$price = new \Product\Price();
			if ($price->getCurrent($this->id)) return $price;
			$this->error($price->error());
			return null;
		}

		/**
		 * Get the current price amount of the product
		 * 
		 * @param array $parameters Optional parameters
		 * @return float|null Price amount or null on error
		 */
        public function getPriceAmount($parameters = array()) {
			$price = new \Product\Price();
			return $price->getCurrent($this->id);
        }

        /**
         * Validate a product code
         * 
         * @param string $string The code to validate
         * @return bool True if valid, false otherwise
         */
        public function validCode($string): bool {
            if (preg_match('/^\w[\w\-\.\_\s]*$/',$string)) return true;
            else return false;
        }

		/**
		 * Validate a product name
		 * 
		 * @param string $string The name to validate
		 * @return bool True if valid, false otherwise
		 */
		public function validName($string): bool {
			if (preg_match('/^[\w\-\.\_\s\:\!]+$/', $string))
				return true;
			else
				return false;
		}	

        /**
         * Validate a product type
         * 
         * @param string $string The type to validate
         * @return bool True if valid, false otherwise
         */
        public function validType($string): bool {
            if (in_array($string,array('group','kit','inventory','unique','service'))) return true;
            else return false;
        }

        /**
         * Validate a product status
         * 
         * @param string $string The status to validate
         * @return bool True if valid, false otherwise
         */
        public function validStatus($string): bool {
            if (in_array($string,array('ACTIVE','HIDDEN','DELETED'))) return true;
            else return false;
        }

		/**
		 * Check if the product is a multi-zone product
		 * 
		 * @return bool True if multi-zone, false otherwise
		 */
		public function isMultiZone() {
			if (preg_match("/(SF|PM|MB)400\-/",$this->code)) return true;
			else return false;
		}

        /**
         * Get the default storage image for the product
         * 
         * @return \Storage\File|null The default image file or null if not found
         */
        public function getDefaultStorageImage() {
            $defaultImageId = $this->getMetadata('default_image');

            if ($defaultImageId) {
               $file = new \Storage\File($defaultImageId);
			   if ($file->id) return $file;
            }
            return null;
        }
	}
