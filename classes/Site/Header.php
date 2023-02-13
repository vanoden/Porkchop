<?php
	namespace Site;

	class Header Extends \BaseClass {
        public $name;
        public $value;

		public function __construct($id = null) {
			$this->_tableName = "site_headers";
			$this->_tableUKColumn = "name";

			if (!empty($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($params = array()) {
			if (empty($params['name'])) {
				$this->error("name required for header");
				return false;
			}
			if (! $this->validName($params['name'])) {
				$this->error("Invalid header name");
				return false;
			}
			if (empty($params['value'])) {
				$this->error("value required for header");
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	site_headers
				(		`name`,
						`value`
				)
				VALUES
				(		?,?)
			";
			$GLOBALS['_database']->Execute($add_object_query,array($params['name'],$params['value']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($params);
		}

		public function update($params): bool {
			$update_object_query = "
				UPDATE	site_headers
				SET		id = id";

			$bind_params = array();
			if (isset($params['value'])) {
				$update_object_query .= ",
						value = ?";
				array_push($bind_params,$params['value']);
			}
			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			return $this->details();
		}

		public function details(): bool {
			$get_object_query = "
				SELECT	*
				FROM	site_headers
				WHERE	id = ?";
			
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->name = $object->name;
				$this->value = $object->value;
			}
			else {
				$this->id = null;
				$this->name = null;
				$this->value = null;
			}
			return true;
		}

		public function validName($string): bool {
			if (preg_match('/^\w[\w\-]*$/',$string)) return true;
			else return false;
		}
	}
?>