<?php
	namespace Site;
	
	class ConfigurationList {
	
		public $error;

		public function find($parameters = array()) {
		
			// Prepare Query
			$get_object_query = "
				SELECT	`key`
				FROM	site_configurations
				WHERE	`key` = `key`
			";
			
			if (isset($parameters['key']))
				$get_object_query .= "
					AND `key` = ".$GLOBALS['_database']->qstr($parameters['key'], get_magic_quotes_gpc());

			if (isset($parameters['value']))
				$get_object_query .= "
					AND `value` = ".$GLOBALS['_database']->qstr($parameters['key'], get_magic_quotes_gpc());
					
			$get_object_query .= "
					ORDER BY `key`
			";
			
			$rs = $GLOBALS['_database']->Execute($get_object_query);
			if (! $rs) {
				$this->error = "SQL Error in ConfigurationList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$pages = array();
			
			while(list($id) = $rs->FetchRow()) {
				$page = new \Site\Configuration($id);
				array_push($pages,$page);
			}
			return $pages;
		}
	}
