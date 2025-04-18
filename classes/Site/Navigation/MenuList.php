<?php
	namespace Site\Navigation;

	class MenuList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\Navigation\Menu';
		}

		public function findAdvanced($parameters,$advanced,$controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build the Query
			$get_menus_query = "
                SELECT  id
                FROM    navigation_menus
                WHERE   id = id
            ";

			// Add Parameters
            if (isset($parameters["title"])) {
				$get_menus_query .= "
                AND     title = ?";
				$database->AddParam($parameters["title"]);
			}

			$rs = $database->Execute($get_menus_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return [];
            }
            $menus = array();
            while(list($id) = $rs->FetchRow()) {
				$this->incrementCount();
				$menu = new Menu($id);
                array_push($menus,$menu);
            }
            return $menus;
        }
	}
