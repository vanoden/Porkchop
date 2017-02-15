<?
	namespace Navigation;

	class MenuList {
		public function find($parameters) {
			$get_menus_query = "
                SELECT  id
                FROM    navigation_menus
                WHERE   id = id
            ";

            if ($parameters["name"]) $get_menus_query .= "
                AND     name = ".$GLOBALS['_database']->qstr($parameters["name"],get_magic_quotes_gpc());

            $rs = $GLOBALS['_database']->Execute($get_menus_query);
            if (! $rs) {
                $this->error = $GLOBALS['_database']->ErrorMsg();
                return null;
            }
            $menus = array();
            while(list($menu_id) = $rs->FetchRow()) {
				$item = new Menu($menu_id);
                array_push($menus,$menu);
            }
            return $menus;
        }
	}
?>