<?php
	namespace Company;

	class DepartmentList {
		public $count;
		public $error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	company_departments
				WHERE	id = id
			";

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "Error finding departments: ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new Department($id);
				push($objects,$object);
			}
		}
	}
?>