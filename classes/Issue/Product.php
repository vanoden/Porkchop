<?php
	namespace Issue;
	
	class Product {
		public $id;
		public $code;
		public $name;
		public $description;
		private $_owner_id;
		private $_error;
		
		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if (! isset($parameters['name'])) {
				$this->_error = "Name required";
				return null;
			}

			if (isset($parameters['owner_id'])) {
				$owner = new \Register\Customer($parameters['owner_id']);
				if (! $owner->id) {
					$this->_error = "Owner not found";
					return null;
				}
			}
			else {
				$this->_error = "Owner must be set";
				return null;
			}
			$this->code = uniqid();

			$add_object_query = "
				INSERT
				INTO	issue_products
				(		code,name,description,owner_id,status)
				VALUES
				(		?,?,?,?,'ACTIVE')
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$this->code,
					$parameters['name'],
					$parameters['description'],
					$owner->id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Issue::Product::add(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	issue_products
				SET		id = id
			";
			
			$update_object_query .= "
				WHERE	id = ?
			";
			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Issue::Product::update(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	issue_products
				WHERE	id = ?
			";

			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);

			if (! $rs) {
				$this->_error = "SQL Error in Issue::Product::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->code = $object->code;
			$this->name = $object->name;
			$this->description = $object->description;
			$this->owner = new \Register\Customer($object->owner_id);
			$this->_owner_id = $this->owner->id;
			
			return $this;
		}

		public function get($code = '') {
			$get_object_query = "
				SELECT	id
				FROM	issue_products
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->_error = "Product not found";
				return null;
			}
			else {
				list($id) = $rs->FetchRow();
				$this->id = $id;
				return $this->details();
			}
		}

		public function error() {
			return $this->_error;
		}
	}
