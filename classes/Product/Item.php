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

		/** @var float The quantity on hand */
		protected float $on_hand = 0.0;

		/** @var float The cost of the quantity on hand */
		protected float $on_hand_cost = 0.0;

		/** @var float The minimum quantity */
		protected float $min_quantity = 0.0;

		/** @var float The maximum quantity */
		protected float $max_quantity = 0.0;

		/** @var int|null The default vendor ID */
		protected ?int $default_vendor = null;

		/** @var float The total quantity purchased */
		protected float $total_purchased = 0.0;

		/** @var float The total cost of the product */
		protected float $total_cost = 0.0;

		/**
		 * Constructor
		 * 
		 * @param int $id Optional product ID
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'product_products';
            $this->_addStatus(array('ACTIVE','HIDDEN','DELETED'));
			$this->_addTypes(array('group','kit','inventory','unique','service'));
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

		/** @method update(parameters)
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

			# Get Current Values for Audit
			$current = new \Spectros\Product\Item($this->id);

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
				"on_hand"		=> '/^\d+(\.\d+)?$/',
				"on_hand_cost"	=> '/^\d+(\.\d+)?$/',
				"min_quantity"	=> '/^\d+(\.\d+)?$/',
				"max_quantity"	=> '/^\d+(\.\d+)?$/',
				"default_vendor" => '/^\d*$/',
				"total_purchased" => '/^\d+(\.\d+)?$/',
				"total_cost" => '/^\d+(\.\d+)?$/'
			);

			# Prepare Query to Update Product
			$update_product_query = "
				UPDATE	product_products
				SET		id = id
			";

			# Loop Through Parameters
			$changed_value = 0;
			$change_description = "";
			foreach (array_keys($parameters) as $parameter) {
				if ($ok_params[$parameter]) {
					if (!isset($parameters[$parameter]) || !preg_match($ok_params[$parameter], $parameters[$parameter])) {
						$this->error("Invalid value for parameter: $parameter");
						return false;
					}
					if ($current->$parameter != $parameters[$parameter]) {
						$changed_value++;
						$change_description .= "Changed $parameter from ".$current->$parameter." to ".$parameters[$parameter]."; ";
					}
					else continue;
					$update_product_query .= ",
					$parameter	= ?";
					$database->AddParam($parameters[$parameter]);
				}
			}

			$update_product_query .= "
				WHERE	id = ?
			";
			$database->AddParam($this->id);

			if ($changed_value == 0) {
				$this->warn("No changes made to product");
				return true;
			}

			$rs = $database->Execute($update_product_query);
            if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				app_log($update_product_query,'debug');
				return false;
            }

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Changes: '.$change_description,
				'class_name' => 'Product\Item',
				'class_method' => 'update'
			));	
			if ($auditLog->error()) {
				$this->error("Failed to log audit event: ".$auditLog->error());
				print_r($auditLog->error());
				return false;
			}

			return $this->details();
		}

		/** @method add(parameters)
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
				'description' => 'Added code: '.$parameters['code'].", type: ".$parameters['type'].", status: ".$parameters['status'],
				'class_name' =>'Product\Item',
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
				$this->on_hand = $product->on_hand ?? 0;
				$this->on_hand_cost = $product->on_hand_cost ?? 0;
				$this->min_quantity = $product->min_quantity ?? 0;
				$this->max_quantity = $product->max_quantity ?? 0;
				$this->default_vendor = $product->default_vendor ?? null;
				$this->total_purchased = $product->total_purchased ?? 0;
				$this->total_cost = $product->total_cost ?? 0;
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
				SELECT	*
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
				$this->on_hand = $object->on_hand ?? 0;
				$this->on_hand_cost = $object->on_hand_cost ?? 0;
				$this->min_quantity = $object->min_quantity ?? 0;
				$this->max_quantity = $object->max_quantity ?? 0;
				$this->default_vendor = $object->default_vendor ?? null;
				$this->total_purchased = $object->total_purchased ?? 0;
				$this->total_cost = $object->total_cost ?? 0;
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
			return $this->get($code);
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

		/** @method getPriceAmount($parameters = array)
		 * Get the current price amount of the product
		 * 
		 * @param array $parameters Optional parameters
		 * @return float|null Price amount or null on error
		 */
        public function getPriceAmount($parameters = array()) {
			$price = new \Product\Price();
			return $price->getCurrent($this->id);
        }

		/** @method minQuantity()
		 * Get the minimum quantity of the product
		 */
		public function minQuantity() {
			return $this->min_quantity;
		}

		/** @method maxQuantity()
		 * Get the maximum quantity of the product
		 */
		public function maxQuantity() {
			return $this->max_quantity;
		}

		/** @method onHand()
		 * Get the quantity on hand of the product
		 */
		public function onHand() {
			return $this->on_hand;
		}

		/** @method onHandCost()
		 * Get the cost of the quantity on hand of the product
		 */
		public function onHandCost() {
			return $this->on_hand_cost;
		}

		/** @method totalPurchased()
		 * Get the total quantity purchased of the product
		 */
		public function totalPurchased() {
			return $this->total_purchased;
		}

		/** @method totalCost()
		 * Get the total cost of the product
		 */
		public function totalCost() {
			return $this->total_cost;
		}

		/** @method defaultVendor()
		 * Get the default vendor ID for the product
		 */
		public function defaultVendor() {
			return $this->default_vendor;
		}

		/** @method vendors()
		 * Get all vendors for the product
		 */
		public function vendors() {
			$vendorList = new \Product\VendorList();
			$vendors = $vendorList->find(array('product_id' => $this->id));
			if ($vendorList->error()) {
				$this->error($vendorList->error());
				return null;
			}
			$item_vendors = array();
			foreach ($vendors as $vendor) {
				if ($vendor->hasItem($this)) {
					$item_vendors[] = $vendor;
				}
				else if ($vendor->error()) {
					$this->error("Error checking vendor ".$vendor->id.": ".$vendor->error());
				}
			}
			return $item_vendors;
		}

		/** @method addVendor(vendor id, parameters)
		 * Add a vendor to the product
		 * @param int $vendor_id The vendor ID
		 * @param array $parameters The vendor parameters
		 * @return bool True if successful, false otherwise
		 */
		public function addVendor($vendor_id, $parameters = array()) {
			// Clear any existing error
			$this->clearError();

			// Initialize the database service
			$database = new \Database\Service();

			// Check Privileges
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->code." failed to add vendor because not have 'manage products' privilege",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return false;
			}

			if (empty($parameters['price_break_quantity_1']) || ! is_numeric($parameters['price_break_quantity_1'])) $parameters['price_break_quantity_1'] = 0;
			if (empty($parameters['price_at_quantity_1']) || ! is_numeric($parameters['price_at_quantity_1'])) $parameters['price_at_quantity_1'] = 0;
			if (empty($parameters['price_break_quantity_2']) || ! is_numeric($parameters['price_break_quantity_2'])) $parameters['price_break_quantity_2'] = 0;
			if (empty($parameters['price_at_quantity_2']) || ! is_numeric($parameters['price_at_quantity_2'])) $parameters['price_at_quantity_2'] = 0;

			// Validate vendor ID
			$vendor = new \Product\Vendor($vendor_id);
			if (! $vendor->id) {
				$this->error("Vendor not found");
				return false;
			}
			$add_vendor_query = "
				INSERT
				INTO	product_vendor_items
				(		vendor_id,
						product_id,
						cost,
						minimum_order,
						vendor_sku,
						pack_quantity,
						pack_unit,
						price_break_quantity_1,
						price_at_quantity_1,
						price_break_quantity_2,
						price_at_quantity_2
				)
				VALUES
				(		?,?,?,?,?,?,?,?,?,?,?
				)
			";
			// Add parameters and execute query
			$database->AddParam($vendor_id);
			$database->AddParam($this->id);
			$database->AddParam($parameters['price'] ?? 0);
			$database->AddParam($parameters['min_order'] ?? 1);
			$database->AddParam($parameters['vendor_sku'] ?? '');
			$database->AddParam($parameters['pack_quantity'] ?? 1);
			$database->AddParam($parameters['pack_unit'] ?? 'each');
			$database->AddParam($parameters['price_break_quantity_1'] ?? 0);
			$database->AddParam($parameters['price_at_quantity_1'] ?? 0);
			$database->AddParam($parameters['price_break_quantity_2'] ?? 0);
			$database->AddParam($parameters['price_at_quantity_2'] ?? 0);

			$database->Execute($add_vendor_query);
			if ($database->error()) {
				$this->error($database->error());
				return false;
			}
			return true;
		}

		/** @method hasVendor(vendor)
		 * Check if the product is available through a specific vendor
		 * @param int $vendor_id The vendor ID
		 */
		public function hasVendor($vendor_id): bool {
			$this->clearError();
			$database = new \Database\Service();
			$check_vendor_query = "
				SELECT	1
				FROM	product_vendor_items
				WHERE	vendor_id = ?
				AND		product_id = ?
			";
			$database->AddParam($vendor_id);
			$database->AddParam($this->id);
			$rs = $database->Execute($check_vendor_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			while ($row = $rs->FetchRow()) {
				if ($row[0] == 1) {
					return true;
				}
			}
			return false;
		}

        /** @method validateCode($string)
         * Validate a product code
         * 
         * @param string $string The code to validate
         * @return bool True if valid, false otherwise
         */
        public function validCode($string): bool {
            if (preg_match('/^\w[\w\-\.\_\s]*$/',$string)) return true;
            else return false;
        }

		/** @method validName($string)
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

        /** @method validType($string)
         * Validate a product type
         * 
         * @param string $string The type to validate
         * @return bool True if valid, false otherwise
         */
        public function validType($string): bool {
            if (in_array($string,array('group','kit','inventory','unique','service'))) return true;
            else return false;
        }

        /** @method validStatus($string)
         * Validate a product status
         * 
         * @param string $string The status to validate
         * @return bool True if valid, false otherwise
         */
        public function validStatus($string): bool {
            if (in_array($string,array('ACTIVE','HIDDEN','DELETED'))) return true;
            else return false;
        }

        /** @method getDefaultStorageImage()
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

		/** @method addPart(product_id, quantity)
		 * Add a part for assembly of product
		 * @param int $product_id The product ID of the part
		 * @param float $quantity The quantity of the part
		 * @return bool True if successful, false otherwise
		 */
		public function addPart($id, $quantity): bool {
			// Clear any existing error
			$this->clearError();
			
			// Check Privileges
			if (! $GLOBALS['_SESSION_']->customer->can('manage products')) {
				$this->error("You do not have permissions for this task.");
				app_log($GLOBALS['_SESSION_']->customer->code." failed to add part because not have 'manage products' privilege",'notice',__FILE__,__LINE__);
				app_log(print_r($GLOBALS['_SESSION_'],true),'debug',__FILE__,__LINE__);
				return false;
			}

			// Validate product ID and quantity
			if (! is_numeric($quantity) || $quantity <= 0) {
				$this->error("Invalid quantity");
				return false;
			}
			$part = new \Product\Item($id);
			if (! $part->id) {
				$this->error("Part not found");
				return false;
			}

			$part = new \Product\Item\Part();
			$part->add(array(
				'product_id' => $this->id,
				'part_product_id' => $id,
				'quantity' => $quantity
			));
			if ($part->error()) {
				$this->error($part->error());
				return false;
			}
			return true;
		}

		/** @method updatePart(parameters)
		 * Update a part for the product
		 * @param array $parameters The parameters for the part
		 * @return bool True if successful, false otherwise
		 */
		public function updatePart($parameters = []): bool {
			$this->clearError();
			$part = new \Product\Item\Part();
			if (! $part->get($this->id, $parameters['part_id'])) {
				$this->error($part->error());
				return false;
			}
			if (! $part->update($parameters)) {
				$this->error($part->error());
				return false;
			}
			return true;
		}

		/** @method deletePart(part_id)
		 * Delete a part from the product
		 * @param int $part_id The part ID to delete
		 * @return bool True if successful, false otherwise
		 */
		public function deletePart($part_id): bool {
			$this->clearError();
			$part = new \Product\Item\Part($part_id);
			if (! $part->delete($part_id)) {
				$this->error($part->error());
				return false;
			}
			return true;
		}

		/** @method parts()
		 * Get all parts for the product
		 * @return array|null Array of part objects or null on error
		 */
		public function parts() {
			$partList = new \Product\Item\PartList();
			$parts = $partList->find(array('product_id' => $this->id));
			if ($partList->error()) {
				$this->error($partList->error());
				return null;
			} else {
				return $parts;
			}
		}

		/** @method variantTypes()
		 * Get a list of valid variant types
		 * @return array Array of variant type strings
		 */
		public function variantTypes(): array {
			return array('none','size','color','shape','material','model','style');
		}

		/** @method validVariantType(string)
		 * Validate the variant type
		 * @param string $type
		 * @return bool
		 */
		public function validVariantType($type) {
			$valid_types = ['none','size','color','shape','material','model','style'];
			return in_array($type, $valid_types);
		}
	}
