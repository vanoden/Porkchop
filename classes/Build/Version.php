<?php
	namespace Build;

	class Version {

		public $id;
		private $_error;

		public function __construct($id = null) {
			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = []) {
			if ($parameters['product_id']) {
				$product = new Product($parameters['product_id']);
				if (! $product->id) {
					$this->_error = "Product not found";
					return false;
				}
			}
			else {
				$this->_error = "Product id required";
			}
			if (! isset($parameters['number']) || ! preg_match('/^\d.*$/',$parameters['number'])) {
				$this->_error = "Number required";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	build_versions
				(		product_id,number,`timestamp`,status)
				VALUES
				(		?,?,sysdate(),'NEW')
			";
			$GLOBALS['_database']->Execute($add_object_query,array($product->id,$parameters['number']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Build::Product::add(): ".$GLOBALS['_database']->ErrorMsg();
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

		public function _objectName() {
			if (!isset($caller)) {
				$trace = debug_backtrace();
				$caller = $trace[2];
			}

			$class = isset($caller['class']) ? $caller['class'] : null;
			if (preg_match('/(\w[\w\_]*)$/',$class,$matches)) $classname = $matches[1];
			else $classname = "Object";
			return $classname;
		}

		public function update($parameters = array()) {
			if (! isset($this->id)) {
				$this->_error = "id required for update";
				return false;
			}

			$bind_params = array();
			$update_object_query = "
				UPDATE	build_versions
				SET		id = id";
			if (isset($parameters['status'])) {
				$update_object_query .= ", status = ?";
				array_push($bind_params,$parameters['status']);
			}
			if (isset($parameters['tarball'])) {
				$update_object_query .= ", tarball = ?";
				array_push($bind_params,$parameters['tarball']);
			}
			if (isset($parameters['message'])) {
				$update_object_query .= ", message = ?";
				array_push($bind_params,$parameters['message']);
			}
			if (isset($parameters['major_number'])) {
				$update_object_query .= ", major_number = ?";
				array_push($bind_params,$parameters['major_number']);
			}
			if (isset($parameters['minor_number'])) {
				$update_object_query .= ", minor_number = ?";
				array_push($bind_params,$parameters['minor_number']);
			}
			if (isset($parameters['user_id'])) {
				$update_object_query .= ", user_id = ?";
				array_push($bind_params,$parameters['user_id']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Build::Version::update(): ".$GLOBALS['_database']->ErrorMsg();
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

		public function get($product_id,$number) {
			$product = new Product($product_id);
			if (! $product->id) {
				$this->_error = "Product not found";
				return false;
			}

			$get_object_query = "
				SELECT	id
				FROM	build_versions
				WHERE	product_id = ?
				AND		number = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($product->id,$number));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Product::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id > 0) {
				app_log("Found version ".$this->id);
				return $this->details();
			}
			return false;
		}
		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	build_versions
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Version::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->product_id = $object->product_id;
				$this->major_number = $object->major_number;
				$this->minor_number = $object->minor_number;
				$this->number = $object->number;
				$this->timestamp = $object->timestamp;
				$this->status = $object->status;
				$this->message = $object->message;
				$this->tarball = $object->tarball;
				$this->user_id = $object->user_id;
				return true;
			}
			else {
				$this->id = null;
				return false;
			}
		}

		public function error() {
			return $this->_error;
		}
	}
