<?php
	namespace Sales\Order;

	class Event extends \BaseModel {
		public $order_id;
		public $user_id;
		public $date_event;
		public $new_status;
		public $message;

		public function __construct($id = 0) {
			$this->_tableName = 'sales_order_events';
			$this->_addFields(array('order_id','user_id','date_event','new_status'));
			parent::__construct($id);
		}

		public function add($parameters = []) {

			$this->clearError();

			$database = new \Database\Service();

			$order = new \Sales\Order($parameters['order_id']);
			if (! $order->exists()) {
				$this->error("Order not found");
				return false;
			}
            if (!isset($parameters['user_id'])) $parameters['user_id'] = $GLOBALS['_SESSION_']->customer->id;
			if (!isset($parameters['new_status'])) $parameters['new_status'] = $order->status;

			$add_object_query = "
				INSERT
				INTO	sales_order_events
				(		id,order_id,date_event,user_id,new_status)
				VALUES
				(		null,?,sysdate(),?,?)
			";
			$database->AddParam($parameters['order_id']);
			$database->AddParam($parameters['user_id']);
			$database->AddParam($parameters['new_status']);

			$database->Execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
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

		public function update($parameters = array()): bool {

			$update_object_query = "
				UPDATE	sales_order_events
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['new_status'])) {
				$update_object_query .= ", new_status = ?";
				array_push($bind_params,$parameters['new_status']);
			}
			if (isset($parameters['abbreviation'])) {
				$update_object_query .= ", abbreviation = ?";
				array_push($bind_params,$parameters['abbreviation']);
			}
            if (isset($parameters['message'])) {
                $update_object_query .= ", message = ?";
                array_push($bind_params,$parameters['message']);
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
				FROM	sales_order_events
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($this->id) {
				app_log("Got details for ".$this->id);
				$this->id = $object->id;
				$this->order_id = $object->order_id;
				$this->user_id = $object->user_id;
				$this->date_event = $object->date_event;
                $this->new_status = $object->new_status;
                $this->message = $object->message;
				return true;
			}
			else {
				return false;
			}
		}
	}
