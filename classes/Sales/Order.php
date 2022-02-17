<?php
	namespace Sales;

use Aws\Emr\Enum\InstanceRoleType;

class Order extends \ORM\BaseModel {
	
		public $id;
		public $customer_id;
		public $salesperson_id;
		public $status;
		public $customer_order_number;
		private $lastID;

		public function add($parameters = array()) {
			$customer = new \Register\Customer($parameters['customer_id']);
			if (! $customer->id) {
				$this->_error = "Customer not found";
				return false;
			}
            if (isset($parameters['salesperson_id'])) {
    			$salesperson = new \Register\Admin($parameters['salesperson_id']);
	    		if (! $salesperson->id) {
		    		$this->_error = "Salesperson not found";
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
				$this->_error = "SQL Error in Sales::Order::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			$this->addEvent(array('new_status' => $status,'user_id' => $GLOBALS['_SESSION_']->customer->id,'type' => "CREATE"));
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
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

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Sales::Order::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->addEvent(array('new_status' => $parameters['status'],'user_id' => $GLOBALS['_SESSION_']->customer->id,'type' => "UPDATE"));
			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	sales_orders
				WHERE	code = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($code));
			if (! $rs) {
				$this->_error = "SQL Error in Sales::Order::get(): ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error("SQL Error in Sales::Order::getByCustomerOrderNumber(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();
			$this->id = $id;
			return $this->details();
		}

		public function details() {
			$get_details_query = "
				SELECT	*
				FROM	sales_orders
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Sales::Order::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($this->id) {
				$this->id = $object->id;
				$this->code = $object->code;
				$this->salesperson_id = $object->salesperson_id;
				$this->status = $object->status;
				$this->customer_id = $object->customer_id;
				$this->customer_order_number = $object->customer_order_number;
				return true;
			} else {
				return false;
			}
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
                    'organization_id' => $customer->organization->id,
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
							app_log("Added ticket ".$ticket->code);
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
				$this->error("SQL Error in Sales::Order::addItem(): ".$GLOBALS['_database']->ErrorMsg());
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

		public function error($error = null) {
            if (isset($error)) $this->_error = $error;
			return $this->_error;
		}

		public function lastItemID() {
			return $this->lastID;
		}
	}
