<?php
	namespace Support\Request\Item;

	class RMA {
	
		private $_error;
		public $code;
		private $approved_id;
		public $date_approved;
		public $timestamp_approved;
		public $item;
		private $item_id;
		public $status;
		public $_exists = false;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				return $this->details();
			}
		}

		public function add($parameters) {
			$approvedBy = new \Register\Customer($parameters['approved_id']);
			if ($approvedBy->error) {
				$this->_error = $approvedBy->error;
				return false;
			}
			if (! $approvedBy->id) {
				$this->_error = "Approver not found";
				return false;
			}

			if (isset($parameters['date_approved'])) {
				if (get_mysql_date($parameters['date_approved'])) {
					$date_approved = get_mysql_date($parameters['date_approved']);
				}
				else {
					$this->_error = "Invalid date for approval";
					return false;
				}
			}
			else {
				$date_approved = date('Y-m-d H:i:s');
			}
			
			$item = new \Support\Request\Item($parameters['item_id']);
			if ($item->error()) {
				$this->_error = $item->error();
				return false;
			}
			if (! $item->id) {
				$this->_error = "Request Item not found";
				return false;
			}

			if (isset($parameters['code'])) {
				$check = new \Support\Request\RMA();
				if ($check->get($parameters['code'])) {
					$this->_error = "Code already used";
					return false;
				}
				else $code = $parameters['code'];
			}
			else {
				$code = uniqid();
			}
			$add_object_query = "
				INSERT
				INTO	support_rmas
				(		code,
						item_id,
						approved_id,
						date_approved,
						status
				)
				VALUES
				(		?,?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$code,
					$item->id,
					$approvedBy->id,
					$date_approved,
					'NEW'
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::RMA::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
		
		public function update($parameters) {
			$update_action_query = "
				UPDATE	`support_rmas`
				SET		id = id
			";

            $bind_params = array();
			if (isset($parameters['status']) && $parameters['status'] != $this->status) {
				$update_action_query .= ",
				status = ?";
				array_push($bind_params,$parameters['status']);
			}

			if (isset($parameters['approved_id']) && $parameters['approved_id'] > 0) {
				$admin = new \Register\Customer($parameters['approved_id']);
				if ($admin->error) {
					$this->_error = $admin->error;
					return false;
				}
				if (! $admin->id) {
					$this->_error = "Admin not found";
					return false;
				}
				if ($admin->id != $this->assignedTo->id) {
					$update_action_query .= ",
						approved_id = ?,
						date_approved = sysdate()";
					array_push($bind_params,$parameters['approved_id']);
				}
			}

			$update_action_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);
	
			$GLOBALS['_database']->Execute($update_action_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::Item::RMA::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}
		
		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	support_rmas
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,array($code)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::RMA::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*,
						unix_timestamp('date_approved') timestamp_approved
				FROM	support_rmas
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,array($this->id)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::RMA::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if (!empty($object)) {
				$this->id = $object->id;
				$this->code = $object->code;
				$this->date_approved = $object->date_approved;
				$this->timestamp_approved = $object->timestamp_approved;
				$this->approved_id = $object->approved_id;
				$this->status = $object->status;
				$this->item_id = $object->item_id;
				$this->_exists = true;
			}
			else {
				$this->id = null;
				$this->code = null;
				$this->status = null;
				$this->item_id = null;
				$this->_exists = false;
			}
			return true;
		}

		public function shipments() {
			$shipmentList = new \Shipping\ShipmentList();
			$shipments = $shipmentList->find(array('document_id' => $this->number()));
			if ($shipmentList->error()) {
				$this->_error = $shipmentList->error();
				return null;
			}
			return $shipments;
		}

		public function approvedBy() {
			return new \Register\Customer($this->approved_id);
		}
		public function document() {
			return new \Storage\File($this->document_id);
		}

		public function item() {
			return new \Support\Request\Item($this->item_id);
		}
		public function number() {
			return sprintf("RMA%05d",$this->id);
		}
		public function events() {
			return null;
		}
		public function localtimeApproved() {
			return date('m/d/Y H:i:s',$this->timestamp_approved);
		}

		public function exists() {
			return $this->_exists;
		}

		public function error() {
			return $this->_error;
		}
	}
