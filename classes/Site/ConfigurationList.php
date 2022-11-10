<?php
	namespace Site;
	
	class ConfigurationList Extends \BaseListClass {

		public function find($parameters = array()) {
            $this->clearError();
            $this->resetCount();

            // Initialize Parameter Array
            $bind_params = array();

			// Prepare Query
			$get_object_query = "
				SELECT	`key`
				FROM	site_configurations
				WHERE	`key` = `key`
			";
			
			if (isset($parameters['key'])) {
				$get_object_query .= "
					AND `key` = ?";
				array_push($bind_params,$parameters['key']);
			}

			if (isset($parameters['value'])) {
				$get_object_query .= "
					AND `value` = ?";
				array_push($bind_params,$parameters['key']);
			}
		
			$get_object_query .= "
					ORDER BY `key`
			";
			
			$rs = $GLOBALS['_database']->Execute($get_object_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$pages = array();
			
			while(list($id) = $rs->FetchRow()) {
				$page = new \Site\Configuration($id);
				$this->incrementCount();
				array_push($pages,$page);
			}
			return $pages;
		}
	}
