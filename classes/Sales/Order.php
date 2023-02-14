<?php
	namespace Sales;

use Aws\Emr\Enum\InstanceRoleType;

class Order extends \BaseModel {

		public $code;
		public $customer_id;
		public $salesperson_id;
		public $status;
		public $customer_order_number;
		public $organization_id;
		public $billing_location_id;
		public $shipping_location_id;
		private $lastID;

		public function __construct($id = 0) {
			$this->_tableName = "sales_orders";
			parent::__construct($id);
		}

		public function add($parameters = []) {
			$customer = new \Register\Customer($parameters['customer_id']);
			if (! $customer->id) {
				$this->error("Customer not found");
				return false;
			}
            if (isset($parameters['salesperson_id'])) {
    			$salesperson = new \Register\Admin($parameters['salesperson_id']);
	    		if (! $salesperson->id) {
		    		$this->error("Salesperson not found");
			    	return false;
			    }
            }
			if ($parameters['status']) $status = $parameters['status'];
			else $status = 'NEW';
			if ($parameters['code']) $code = $parameters['code'];
			else $code = uniqid();

			$add_object_query = "
				INSERT
				INTO	sales_orders
				(		id,code,customer_id,salesperson_id,status)
				VALUES
				(		null,?,?,?,?)
			";
			$bind_params = array($code,$customer->id,$salesperson->id,$status);
            query_log($add_object_query,$bind_params);
			$GLOBALS['_database']->Execute($add_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			$this->addEvent(array('new_status' => $status,'user_id' => $GLOBALS['_SESSION_']->customer->id,'type' => "CREATE"));
			return $this->update($parameters);
		}

		public function update($parameters = []): bool {
		
			$update_object_query = "
				UPDATE	sales_orders
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['status'])) {
				$update_object_query .= ", status = ?";
				array_push($bind_params,$parameters['status']);
			}
			if (isset($parameters['customer_order_number'])) {
				$update_object_query .= ", customer_order_number = ?";
				array_push($bind_params,$parameters['customer_order_number']);
			}

			if (isset($parameters['organization_id'])) {
				$update_object_query .= ", organization_id = ?";
				array_push($bind_params,$parameters['organization_id']);
			}

			if (isset($parameters['customer_id'])) {
				$update_object_query .= ", customer_id = ?";
				array_push($bind_params,$parameters['customer_id']);
			}		
		
			if (isset($parameters['salesperson_id'])) {
				$update_object_query .= ", salesperson_id = ?";
				array_push($bind_params,$parameters['salesperson_id']);
			}
			
			if (isset($parameters['billing_location_id'])) {
				$update_object_query .= ", billing_location_id = ?";
				array_push($bind_params,$parameters['billing_location_id']);
			}
			
			if (isset($parameters['shipping_location_id'])) {
				$update_object_query .= ", shipping_location_id = ?";
				array_push($bind_params,$parameters['shipping_location_id']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->addEvent(array('new_status' => $parameters['status'],'user_id' => $GLOBALS['_SESSION_']->customer->id,'type' => "UPDATE"));
			return $this->details();
		}

		public function get($code) : bool {

			$get_object_query = "
				SELECT	id
				FROM	sales_orders
				WHERE	code = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($code));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id) {
				return $this->details();
			} else {
				return false;
			}
		}

		public function getByCustomerOrderNumber($customer_id,$number) {
			$get_order_query = "
				SELECT	id
				FROM	sales_orders
				WHERE	customer_id = ?
				AND		customer_order_number = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_order_query,array($customer_id,$number));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function salesperson() {
			return new \Register\Admin($this->salesperson_id);
		}

		public function customer() {
			return new \Register\Customer($this->customer_id);
		}

        public function approve() {
            if ($this->update(array('status' => 'APPROVED'))) {
                $this->addEvent(array('order_id' => $this->id, 'new_status' => 'APPROVED'));
                $customer = new \Register\Customer($this->customer_id);
                $service_request = new \Support\Request();
                if ($service_request->add(array(
                    'customer_id' => $customer->id,
                    'organization_id' => $customer->organization()->id,
                    'type'          => 'ORDER',
                    'code'          => 'SO_'.$this->id
                ))) {
					app_log("Created request ".$service_request->code);
					$line = 0;
                    foreach ($this->items() as $item) {
						app_log("Adding product ".$item->product_id);
						$line ++;
                        if ($ticket = $service_request->addItem(array(
                            'product_id'    => $item->product_id,
                            'quantity'      => $item->quantity,
                            'status'        => 'NEW',
                            'description'   => $item->description,
							'line'			=> $line
                        ))) {
							app_log("Added item ".$item->product_id);
						}
						else {
							$this->error($service_request->error());
							return false;
						}
                    }
                }
				return true;
			}
			else {
				return false;
			}
        }

		public function cancel($reason = '') {
			if (! $this->update(array('status' => 'CANCELLED','message' => $reason))) return false;
			if (! $this->addEvent(
				array(
					'order_id' => $this->id,
					'user_id' => $GLOBALS['_SESSION_']->customer->id,
					'new_status' => 'CANCELLED',
					'message'	=> $reason
				)
			)) return false;
			return true;
		}

		private function nextNumber() {
			$get_number_query = "
				SELECT	max(line_number)
				FROM	sales_order_items
				WHERE	order_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_number_query,array($this->id));
			if (! $rs) {
				$this->error("SQL Error in Sales::Order::nextNumber(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($number) = $rs->FetchRow();
			return $number + 1;
		}

		public function addItem($parameters) {
			if (empty($parameters['price'])) {
				$this->error("Price required for line item");
				return null;
			}
			$insert_item_query = "
				INSERT
				INTO	sales_order_items
				(		order_id,
						line_number,
						product_id,
						description,
						quantity,
						unit_price,
						status
				)
				VALUES
				(		?,?,?,?,?,?,'OPEN')
			";
			$parameters['line_number'] = $this->nextNumber();
			$bind_params = array(
				$this->id,
				$parameters['line_number'],
				$parameters['product_id'],
				$parameters['description'],
				$parameters['quantity'],
				$parameters['price']
			);
			query_log($insert_item_query,$bind_params,true);
			$GLOBALS['_database']->Execute($insert_item_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				app_log("Error: ".$this->error(),'error');
				return false;
			}
			$this->lastID = $GLOBALS['_database']->Insert_ID();
			return true;
		}

		public function getItem($line_number) {
			return new \Sales\Order\Item($this->id,$line_number);
		}

		public function dropItem($line_number) {
			$item = new \Sales\Order\Item($this->id,$line_number);
			return $item->update(array('status' => 'VOID'));
		}

		public function items($parameters = array()) {
			$parameters['order_id'] = $this->id;
			$itemList = new Order\ItemList();
			$items = $itemList->find($parameters);
			if ($itemList->error()) {
				$this->error($itemList->error());
				return null;
			}
			else {
				return $items;
			}
		}

		private function addEvent($parameters = array()) {
			$event = new \Sales\Order\Event();
			$parameters['order_id'] = $this->id;
			if (! $event->add($parameters)) {
				$this->error($event->error());
				return false;
			}
		}

		public function lastItemID() {
			return $this->lastID;
		}
	}
