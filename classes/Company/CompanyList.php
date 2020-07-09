<?php
	namespace Company;

	class CompanyList {
		public $count;
		public $error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	company_companies
				WHERE	id = id";
			
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in Site::Company::find(): ".$GLOBALS['_database']->ErrorMsg();
				return undef;
			}
			
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$company = new Company($id);
				array_push($objects,$company);
				$this->count ++;
			}
			return $objects;
		}

		public function error() {
			return $this->error;
		}
	}
