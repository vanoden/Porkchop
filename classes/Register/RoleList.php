<?php
	namespace Register;

	class RoleList Extends \BaseListClass {
		public function find($parameters = array()) {
			$get_objects_query = "
				SELECT	id
				FROM	register_roles
				WHERE	id = id
			";
			$rs = $GLOBALS['_database']->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$roles = array();
			while (list($id) = $rs->FetchRow()) {
				$role = new Role($id);
				$this->incrementCount();
				array_push($roles,$role);
			}
			return $roles;
		}
	}
