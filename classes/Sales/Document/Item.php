<?php
	namespace Sales\Document;

	class Item extends \BaseModel {
	
        public $order_id;
        public $line_number;
        public $product_id;
        public $serial_number;
        public $description;
        public $quantity;
        public $unit_price;
        public $status;
        public $cost;
		
		public function __construct($id = 0) {
			$this->_tableName = 'sales_order_items';
			$this->_addFields(array('id','order_id','line_number','product_id','serial_number','description','quantity','unit_price','status','cost'));
			$this->_addStatus(array('OPEN','VOID','FULFILLED','RETURNED'));
			parent::__construct($id);
		}
		
		public function add($parameters = []) {
			// Clear Prevoius Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Validate Required Parameters
			$product = new \Product\Item($parameters['product_id']);
			if (! $product->id) {
				$this->error("Product not found");
				return false;
			}
			
			$order = new \Sales\Document($parameters['order_id']);
			if (! $order->id) {
				$this->error("Sales Document not found");
				return false;
			}			

			// Get the previous line number
			$line_number = $order->maxLineNumber();

			// Prepare the query to add the new item
			$add_object_query = "
				INSERT
				INTO	sales_order_items
				(		id,order_id,line_number,product_id)
				VALUES
				(		null,?,?,?)
			";

			// Bind Parameters
			$database->AddParam($order->id);
			$database->AddParam($line_number+1);
			$database->AddParam($product->id);
			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$this->id = $database->Insert_ID();

            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

			return $this->update($parameters);
		}

		public function update($parameters = array()): bool {
			// Clear Previous Errors
			$this->clearError();

			// Prepare Database Service
			$database = new \Database\Service();

			// Validate Required Parameters
			if (! $this->id) {
				$this->error("Item ID is required for update");
				return false;
			}

			// Prepare the update query
			$update_object_query = "
				UPDATE	sales_order_items
				SET		id = id";

			if (isset($parameters['product_id'])) {
				$product = new \Product\Item($parameters['product_id']);
				if (! $product->id) {
					$this->error("Product not found");
					return false;
				}
				$update_object_query .= ", product_id = ?";
				$database->AddParam($product->id);
			}
			if (isset($parameters['quantity'])) {
				$update_object_query .= ", quantity = ?";
				$database->AddParam($parameters['quantity']);
			}
			if (isset($parameters['serial_number'])) {
				$update_object_query .= ", serial_number = ?";
				$database->AddParam($parameters['serial_number']);
			}
			if (isset($parameters['description'])) {
				$update_object_query .= ", description = ?";
				$database->AddParam($parameters['description']);
			}
			if (isset($parameters['unit_price'])) {
				$update_object_query .= ", unit_price = ?";
				$database->AddParam($parameters['unit_price']);
			}
			if (isset($parameters['status'])) {
				$update_object_query .= ", status = ?";
				$database->AddParam($parameters['status']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
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

		public function details(): bool {
			$get_details_query = "
				SELECT	*
				FROM	sales_order_items
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object) {
				$this->id = $object->id;
				$this->line_number = $object->line_number;
				$this->product_id = $object->product_id;
				$this->serial_number = $object->serial_number;
				$this->description = $object->description;
				$this->quantity = $object->quantity;
				$this->unit_price = $object->unit_price;
				return true;
			}
			else {
				print_r("Oh noes! No item found ".$this->id."\n");
				return false;
			}
		}

		/** @method total
		 * Calculate the total price for this item
		 * @return float Total price for the item
		 */
		public function total() {
			return $this->quantity * $this->unit_price;
		}

		/** @method public product()
		 * Get the product associated with this item
		 * @return \Product\Item Product object
		 */
		public function product() {
			return new \Product\Item($this->product_id);
		}

		/** @method public order()
		 * Get the order associated with this item
		 * @return \Sales\Document Document object
		 */
		public function order() {
			return new \Sales\Document($this->order_id);
		}
	}
