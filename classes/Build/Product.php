<?php
	namespace Build;

	class Product extends \BaseModel {
		
		public $name;
		public $major_version;
		public $minor_version;
		public $workspace;
		public $architecture;
		public $description;

		public function __construct($id = null) {
			if (isset($id) && is_numeric($id)) {
				$this->id = (int)$id;
				$this->details();
			}
		}

		public function add($parameters = []) {
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

		public function update($parameters = array()): bool {
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

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	build_products
				WHERE	name = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($name));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$row = $rs->FetchRow();
			if (!$row) {
				$this->id = 0;
				return false;
			}

			$this->id = (int)$row[0];
			if ($this->id > 0) {
				app_log("Found product ".$this->id);
				return $this->details();
			}
			return false;
		}
		public function details(): bool {
			$get_object_query = "
				SELECT	*
				FROM	build_products
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
				$this->id = 0;
				return false;
			}
		}

		public function lastVersion() {
			
			if (!isset($this->id) || !is_numeric($this->id)) {
				$this->_error = "Invalid product ID";
				return null;
			}

			$get_object_query = "
				SELECT  id
				FROM    build_versions
				WHERE   product_id = ?
				AND     status = 'PUBLISHED'
				ORDER BY number DESC
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$row = $rs->FetchRow();
			if (!$row) {
				return false;
			}
			$version_id = (int)$row[0];
			return new Version($version_id);
		}

		/**
		 * Validate a product name
		 * 
		 * @param string $string The name to validate
		 * @return bool True if valid, false otherwise
		 */
		public function validName($string): bool {
			if (preg_match('/^[\w\-\.\_\s\:\!]+$/', $string))
				return true;
			else
				return false;
		}

		/**
		 * Validate a product code
		 * 
		 * @param string $string The code to validate
		 * @return bool True if valid, false otherwise
		 */
		public function validCode($string): bool {
			if (preg_match('/^\w[\w\-\.\_\s]*$/',$string)) return true;
			else return false;
		}

		/**
		 * Validate a text string
		 * 
		 * @param string $string The text to validate
		 * @return bool True if valid, false otherwise
		 */
		public function validText($string): bool {
			return ctype_print($string);
		}

		/**
		 * Validate an integer
		 * 
		 * @param string $string The integer to validate
		 * @return bool True if valid, false otherwise
		 */
		public function validInteger($string): bool {
			return is_numeric($string) && filter_var($string, FILTER_VALIDATE_INT) !== false;
		}
	}
