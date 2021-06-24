<?php
	namespace Geography;

	class AdminList {
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

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in Geography::AdminList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$admins = array();
			while(list($id) = $rs->FetchRow()) {
				$admin = new Admin($id);
				array_push($admins,$admin);
				$this->_count ++;
			}
			return $admins;
		}
	}