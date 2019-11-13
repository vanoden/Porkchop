<?php
	namespace Geography;

	class ProvinceList {
		private $_error;
		private $_count = 0;

		public function find ($parameters) {
			$find_objects_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	id = id
			";

			$bind_params = array();

			if (isset($parameters['country_id'])) {
				$find_objects_query .= "
				AND		country_id = ?";
				array_push($bind_params,$parameters['country_id']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$parameters);
			if (! $rs) {
				$this->_error = "SQL Error in Geography::ProvinceList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$provinces = array();
			while(list($id) = $rs->FetchRow()) {
				$province = new Province($id);
				array_push($provinces,$province);
				$this->_count ++;
			}
			return $provinces;
		}

		public function count() {
			return $this->_count;
		}

		public function error() {
			return $this->_error;
		}
	}
