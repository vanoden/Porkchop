<?php
	namespace Site;
		
	class ConfigurationList Extends \BaseListClass {

		public function find($parameters = array()) {
            $this->clearError();
            $this->resetCount();

			$database = new \Database\Service();
			$database->debug = 'log';

			// Prepare Query
			$get_object_query = "
				SELECT	`key`
				FROM	site_configurations
				WHERE	`key` = `key`
			";
			
			if (!empty($parameters['key'])) {
				$get_object_query .= "
					AND `key` = ?";
				$database->addParam($parameters['key']);
			}

			if (!empty($parameters['value'])) {
				$get_object_query .= "
					AND `value` = ?";
				$database->addParam($parameters['value']);
			}
		
			$get_object_query .= "
					ORDER BY `key`
			";
			
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
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
