<?php
	namespace Engineering;

	class RoleList {
	
		public $count;
		public $_error;

		public function find($parameters = array()) {
		
			$get_objects_query = "
				SELECT	id
				FROM	engineering_roles
				WHERE	id = id
			";
			$rs = $GLOBALS['_database']->Execute($get_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in EngineeringRole::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$roles = array();
			while (list($id) = $rs->FetchRow()) {
				$role = new \Engineering\Role($id);
				$this->count ++;
				array_push($roles,$role);
			}
			return $roles;
		}
		public function error() {
			return $this->_error;
		}
	}
