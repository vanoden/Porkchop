<?php
	namespace Site;

	class Header Extends \BaseClass {
		public function __construct($id = null) {
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
			if (! preg_match('/^\w[\w\-]*$/',$params['name'])) {
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
				$this->SQLError($GLOBALS['_database_']->ErrorMsg());
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($params);
		}

		public function update($params) {
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

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	site_headers
				WHERE	id = ?";
			
			$rs = $GLOBALS['_database']->Execute($get_oject_query,array($this->id));
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
	}
?>