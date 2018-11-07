<?php
	namespace Support\Request;
	
	class Item {
		private $_error;
		public $line;
		public $request;
		public $product;
		public $serial_number;
		public $description;
		public $status;
		public $assigned;

		public function __construct($id = 0) {
			if (is_numeric($id)) {
				if ($id > 0) {
					$this->id = $id;
					$this->details();
				}
			}
			else {
				$this->_error = "Invalid id";
			}
		}

		public function add($parameters) {
			if (! isset($parameters['line'])) {
				$this->_error = "line number required";
				return false;
			}
			if (! is_numeric($parameters['line'])) {
				$this->_error = "line must be a number";
				return false;
			}

			if (! isset($parameters['request_id'])) {
				$this->_error = "request_id required";
				return false;
			}
			$request = new \Support\Request($parameters['request_id']);
			
			if ($request->error()) {
				$this->_error = "Error finding request: ".$request->error();
				return false;
			}
			if (! $request->id) {
				$this->_error = "Request not found";
				return false;
			}

			$add_item_query = "
				INSERT
				INTO	support_request_items
				(		request_id,line,product_id,serial_number,quantity,description)
				VALUES
				(		?,?,?,?,?,?)
			";
			query_log($add_item_query);
			$GLOBALS['_database']->Execute(
				$add_item_query,
				array(
					$parameters['request_id'],
					$parameters['line'],
					$parameters['product_id'],
					$parameters['serial_number'],
					$parameters['quantity'],
					$parameters['description']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::Item::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			app_log("Added support item $id");
			return $this->update($parameters);
		}
		public function update($parameters) {
			$update_object_query = "
				UPDATE	support_request_items
				SET		id = id
			";
			$bind_params = array();
			if (isset($parameters['status']) && $parameters['status'] != $this->status) {
				$update_object_query .= ",
						status = ?";
				array_push($bind_params,$parameters['status']);
			}
			if (isset($parameters['serial_number']) && $parameters['serial_number'] != $this->serial_number) {
				$update_object_query .= ",
						serial_number = ?";
				array_push($bind_params,$parameters['serial_number']);
			}
			if (isset($parameters['product_id']) && $parameters['product_id'] != $this->product->id) {
				$product = new \Product\Item($parameters['product_id']);
				if ($product->error) {
					$this->_error = $product->error;
					return false;
				}
				if (! $product->id) {
					$this->_error = "Product not found";
					return false;
				}
				$update_object_query .= ",
						product_id = ?";
				array_push($bind_params,$parameters['product_id']);
			}
			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);
			query_log($update_object_query);	
			$GLOBALS['_database']->Execute(
				$update_object_query,$bind_params
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::Item::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}
		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	support_request_items
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::Item::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->line = $object->line;
			$this->product = new \Product\Item($object->product_id);
			$this->serial_number = $object->serial_number;
			$this->quantity = $object->quantity;
			$this->description = $object->description;
			$this->status = $object->status;
			$this->assigned = new \Register\Customer($object->assigned_id);
			$this->request = new \Support\Request($object->request_id);
			
			return true;
		}
		public function addAction($parameters) {
			$parameters['item_id'] = $this->id;
			$action = new \Support\Request\Item\Action();
			if ($action->add($parameters)) {
				return $action;
			}
			else {
				$this->_error = $action->error();
				return false;
			}
		}
		public function addRMA($parameters) {
			$parameters['item_id'] = $this->id;
			$rma = new \Support\Request\Item\RMA();
			if ($rma->add($parameters)) {
				return $rma;
			}
			else {
				$this->_error = $rma->error();
				return false;
			}
		}
		public function addComment($parameters) {
			$parameters['item_id'] = $this->id;
			if ($parameters['status'] != $this->status) {
				$this->update(array('status' => $parameters['status']));
			}
			$comment = new \Support\Request\Item\Comment();
			if ($comment->add($parameters)) {
				return $comment;
			}
			else {
				$this->_error = $comment->error();
				return false;
			}
		}
		public function error() {
			return $this->_error;
		}
		public function statuses() {
			return array(
					"NEW",
					"CANCELLED",
					"ASSIGNED",
					"OPEN",
					"PENDING CUSTOMER",
					"PENDING VENDOR",
					"COMPLETE",
					"CLOSED"
			);
		}
		public function openActions() {
			$actionlist = new \Support\Request\Item\ActionList();
			$actions = $actionlist->find(array('item_id' => $this->id));
			$count = 0;
			foreach ($actions as $action) {
				if (! in_array($action->status,array('COMPLETE','CLOSED','CANCELLED'))) $count ++;
			}
			return $count;
		}
		public function ticketNumber() {
			return sprintf("%06d",$this->id);
		}
	}
?>
