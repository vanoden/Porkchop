<?php
	namespace Build;

	class VersionList {
		private $_error;
		private $_count = 0;

		public function find ($parameters) {
			$find_objects_query = "
				SELECT	id
				FROM	build_versions
				WHERE	id = id
			";

			$bind_params = array();
			if (isset($parameters['product_id'])) {
				$find_objects_query .= "
				AND		product_id = ?";
				array_push($bind_params,$parameters['product_id']);
			}
			if (isset($parameters['status'])) {
				$find_objects_query .= "
				AND		status = ?";
				array_push($bind_params,$parameters['status']);
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Build::VersionList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$versions = array();
			while(list($id) = $rs->FetchRow()) {
				$version = new Version($id);
				array_push($versions,$version);
				$this->_count ++;
			}
			return $versions;
		}
	}