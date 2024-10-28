<?php
	namespace Geography;

	class AdminList Extends \BaseListClass {
		public function find ($parameters = []) {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$find_objects_query = "
				SELECT	id
				FROM	geography_provinces
				WHERE	id = id
			";

			if (isset($parameters['country_id'])) {
				$find_objects_query .= "
				AND		country_id = ?";
				$database->AddParam($parameters['country_id']);
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
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