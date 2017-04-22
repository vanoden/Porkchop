<?
	namespace Storage;

	class Repository {
		public $error;
		public $name;
		public $type;
		public $id;
		public $code;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			if (! isset($parameters['code']) || ! strlen($parameters['code'])) {
				$parameters['code'] = uniqid();
			}
			if (! $this->_valid_code($parameters['code'])) {
				$this->error = "Invalid code";
				return false;
			}
			if (! isset($paramters['status']) || ! strlen($parameters['status'])) {
				$parameters['status'] = 'NEW';
			}
			else if (! $this->_valid_status($parameters['status'])) {
				$this->error = "Invalid status";
				return false;
			}
			if (! $this->_valid_name($parameters['name'])) {
				$this->error = "Invalid name";
				return false;
			}
	
			$add_object_query = "
				INSERT
				INTO	storage_repositories
				(		code,name,type,status)
				VALUES
				(		?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$parameters['code'],
					$parameters['name'],
					$parameters['type'],
					$parameters['status']
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::Repository::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $GLOBALS['_database']->Insert_ID();

			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	storage_repositories
				SET		id = id
			";
			if (isset($parameters['name'])) {
				if ($this->_valid_name($parameters['name'])) {
					$update_object_query .= ",
					name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());
				}
				else {
					$this->error = "Invalid name '".$parameters['name']."'";
					return false;
				}
			}
			if (isset($parameters['type'])) {
				if ($this->_valid_type($parameters['type'])) {
					$update_object_query .= ",
					type = ".$GLOBALS['_database']->qstr($parameters['type'],get_magic_quotes_gpc());
				}
				else {
					$this->error = "Invalid type";
					return false;
				}
			}
			if (isset($parameters['status'])) {
				if ($this->_valid_status($parameters['status'])) {
					$update_object_query .= ",
					status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
				}
				else {
					$this->error = "Invalid status";
					return false;
				}
			}
			$update_object_query .= "
				WHERE	id = ?
			";

			$GLOBALS['_database']->Execute(
				$update_object_query,
				array($this->id)
			);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::Repository::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}
	
		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	storage_repositories
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->error = "SQL Error in Storage::Repository::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	storage_repositories
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Storage::Repository::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$this->name = $object->name;
			$this->type = $object->type;
			$this->endpoint = $object->endpoint;
			$this->code = $object->code;
		}
		public function _setMetadata($key,$value) {
			$set_object_query = "
				INSERT
				INTO	storage_repository_metadata
				(repository_id,`key`,value)
				VALUES	(?,?,?)
				ON DUPLICATE KEY UPDATE
				value = ?
			";
			$GLOBALS['_database']->Execute(
				$set_object_query,
				array($this->id,$key,$value,$value)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Storage::Repository::setMetadata: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return 1;
		}
		public function _metadata($key) {
			$get_value_query = "
				SELECT	value
				FROM	storage_repository_metadata
				WHERE	repository_id = ?
				AND		`key` = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_value_query,
				array($this->id,$key)
			);
			if (! $rs) {
				$this->error = "SQL Error in Storage::Repository::metadata(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			list($value) = $rs->FetchRow();
			return $value;
		}
		private function _valid_code($string) {
			if (preg_match('/^\w[\w\-\_\.]*$/',$string)) {
				return true;
			}
			return false;
		}
		private function _valid_name($string) {
			if (preg_match('/^\w[\w\-\_\.\s]*$/',$string)) {
				return true;
			}
			return false;
		}
		private function _valid_status($string) {
			if (preg_match('/^(NEW|ACTIVE|HIDDEN)$/',$string)) {
				return true;
			}
			return false;
		}
		private function _valid_type($string) {
			if (preg_match('/^(Local|S3)$/',$string)) {
				return true;
			}
			return false;
		}
	}
?>