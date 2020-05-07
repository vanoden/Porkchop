<?php
	namespace Register;

	class RoleList {
		public $count;
		public $_error;

		public function find($parameters = array()) {
			$get_objects_query = "
				SELECT	id
				FROM	register_roles
				WHERE	id = id
			";
			$rs = $GLOBALS['_database']->Execute($get_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in RegisterRole::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$roles = array();
			while (list($id) = $rs->FetchRow()) {
				$role = new Role($id);
				$this->count ++;
				array_push($roles,$role);
			}
			return $roles;
		}
		public function error() {
			return $this->_error;
		}
	}
