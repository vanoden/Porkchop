<?php
	namespace Build;

	class Repository {
		public $id;
		private $_error;

		public function __construct($id = null) {
			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = []) {
			if (! isset($parameters['url']) || ! strlen($parameters['url'])) {
				$this->_error = "url required";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	build_repositories
				(		id,url)
				VALUES
				(		null,?)
			";
			$GLOBALS['_database']->Execute($add_object_query,array($parameters['url']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Build::Repository::add(): ".$GLOBALS['_database']->ErrorMsg();
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
			return $this->details();
		}

		public function get($url) {
			$get_object_query = "
				SELECT	id
				FROM	build_repositories
				WHERE	url = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($url));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Repository::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id > 0) {
				app_log("Found repository ".$this->id);
				return $this->details();
			}
			return false;
		}
		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	build_repositories
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Repository::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->url = $object->url;
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
