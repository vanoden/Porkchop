<?php
	namespace Company;

	class CompanyList Extends \BaseListClass {
		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	company_companies
				WHERE	id = id";
			
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$company = new Company($id);
				array_push($objects,$company);
				$this->incrementCount();
			}
			return $objects;
		}
	}
