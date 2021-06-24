<?php
	namespace Build;

	class Product {
		public $id;
		private $_error;

		public function __construct($id = null) {
			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			if (! isset($parameters['name']) || ! preg_match('/^\w.*$/',$parameters['name'])) {
				$this->_error = "Name required";
				return false;
			}
			if ($this->get($parameters['name'])) {
				$this->_error = "Product already exists";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	build_products
				(		name,architecture,major_version,minor_version)
				VALUES
				(		?,?,?,?)
			";
			$GLOBALS['_database']->Execute($add_object_query,array($parameters['name'],$parameters['architecture'],$parameters['major_version'],$parameters['minor_version']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Build::Product::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			if (! isset($this->id)) {
				$this->_error = "id required for update";
				return false;
			}

			$bind_params = array();
			$update_object_query = "
				UPDATE	build_products
				SET		id = id";
			if (isset($parameters['name'])) {
				$update_object_query .= ", name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['architecture'])) {
				$update_object_query .= ", architecture = ?";
				array_push($bind_params,$parameters['architecture']);
			}
			if (isset($parameters['major_version'])) {
				$update_object_query .= ", major_version = ?";
				array_push($bind_params,$parameters['major_version']);
			}
			if (isset($parameters['minor_version'])) {
				$update_object_query .= ", minor_version = ?";
				array_push($bind_params,$parameters['minor_version']);
			}
			if (isset($parameters['workspace'])) {
				$update_object_query .= ", workspace = ?";
				array_push($bind_params,$parameters['workspace']);
			}
			if (isset($parameters['description'])) {
				$update_object_query .= ", description = ?";
				array_push($bind_params,$parameters['description']);
			}
			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Build::Product::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	build_products
				WHERE	name = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($name));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Product::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id > 0) {
				app_log("Found product ".$this->id);
				return $this->details();
			}
			return false;
		}
		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	build_products
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Product::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->major_version = $object->major_version;
				$this->minor_version = $object->minor_version;
				$this->workspace = $object->workspace;
				$this->architecture = $object->architecture;
				$this->description = $object->description;
				return true;
			}
			else {
				$this->id = null;
				return false;
			}
		}

		public function lastVersion() {
			$get_last_query = "
				SELECT	id
				FROM	build_versions
				WHERE	product_id = ?
				ORDER BY `timestamp` DESC
				LIMIT	1
			";
			$rs = $GLOBALS['_database']->Execute($get_last_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Product::lastVersion(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($id) = $rs->FetchRow();
			return new Version($id);
		}

		public function error() {
			return $this->_error;
		}
	}