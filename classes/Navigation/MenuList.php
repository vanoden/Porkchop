<?php
	namespace Navigation;

	class MenuList Extends \BaseListClass {

		public function find($parameters = array()) {
			$get_menus_query = "
                SELECT  id
                FROM    navigation_menus
                WHERE   id = id
            ";
			$bind_params = array();

            if (isset($parameters["title"])) {
				$get_menus_query .= "
                AND     title = ?";
				array_push($bind_params,$parameters["title"]);
			}
			query_log($get_menus_query,$bind_params);
            $rs = $GLOBALS['_database']->Execute($get_menus_query,$bind_params);
            if (! $rs) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return null;
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
