<?php
	namespace Geography;

	class CountryList {
		private $_error;
		private $_count = 0;

		public function find ($parameters) {
			$find_objects_query = "
				SELECT	id
				FROM	geography_countries
			";

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in Geography::CountryList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$countries = array();
			while(list($id) = $rs->FetchRow()) {
				$country = new Country($id);
				array_push($countries,$country);
				$this->_count ++;
			}
			return $countries;
		}
	}