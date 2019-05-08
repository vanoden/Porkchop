<?
	namespace Navigation;

	class MenuList {
		private $_error;
		private $_count = 0;

		public function find($parameters) {
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
			query_log($get_menus_query);
            $rs = $GLOBALS['_database']->Execute($get_menus_query,$bind_params);
            if (! $rs) {
                $this->_error = $GLOBALS['_database']->ErrorMsg();
                return null;
            }
            $menus = array();
            while(list($id) = $rs->FetchRow()) {
				$this->_count ++;
				$menu = new Menu($id);
                array_push($menus,$menu);
            }
            return $menus;
        }

		public function count() {
			return $this->_count;
		}

		public function error() {
			return $this->_error;
		}
	}
?>