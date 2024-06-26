<?php
	namespace Sales\Order;

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
		
			$product = new \Product\Item($parameters['product_id']);
			if (! $product->id) {
				$this->error("Product not found");
				return false;
			}
			
			$salesOrder = new \Sales\Order($parameters['order_id']);
			if (! $salesOrder->id) {
				$this->error("Sales Order not found");
				return false;
			}			
			
			$line_number = $this->maxLineNumberByOrder($salesOrder->id);
			
			$add_object_query = "
				INSERT
				INTO	sales_order_items
				(		id,order_id,line_number,product_id)
				VALUES
				(		null,?,?,?)
			";
			
			$GLOBALS['_database']->Execute($add_object_query,array($salesOrder->id,($line_number+1),$product->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();

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
		
        /**
         * get max value from a sales order line number based on the order it's in
         */
		public function maxLineNumberByOrder($salesOrderId) {
		
			$database = new \Database\Service();
			$get_object_query = "SELECT MAX(`line_number`) FROM `$this->_tableName` WHERE `order_id` = " . $salesOrderId;

			$rs = $database->Execute($get_object_query);
			if (!$rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}

		public function update($parameters = array()): bool {
			$update_object_query = "
				UPDATE	sales_order_items
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['product_id'])) {
				$product = new \Product\Item($parameters['product_id']);
				if (! $product->id) {
					$this->error("Product not found");
					return false;
				}
				$update_object_query .= ", product_id = ?";
				array_push($bind_params,$product->id);
			}
			if (isset($parameters['quantity'])) {
				$update_object_query .= ", quantity = ?";
				array_push($bind_params,$parameters['quantity']);
			}
			if (isset($parameters['serial_number'])) {
				$update_object_query .= ", serial_number = ?";
				array_push($bind_params,$parameters['serial_number']);
			}
			if (isset($parameters['description'])) {
				$update_object_query .= ", description = ?";
				array_push($bind_params,$parameters['description']);
			}
			if (isset($parameters['unit_price'])) {
				$update_object_query .= ", unit_price = ?";
				array_push($bind_params,$parameters['unit_price']);
			}
			if (isset($parameters['status'])) {
				$update_object_query .= ", status = ?";
				array_push($bind_params,$parameters['status']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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

		public function total() {
			return $this->quantity * $this->unit_price;
		}

		public function product() {
			return new \Product\Item($this->product_id);
		}
	}
