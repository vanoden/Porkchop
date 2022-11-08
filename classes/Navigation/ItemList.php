<?php
	namespace Navigation;

	class ItemList Extends \BaseListClass {
		public function find($parameters = array()) {
			$get_items_query = "
				SELECT  id
				FROM    navigation_menu_items
				WHERE   id = id
			";
			$bind_params = array();

			if ($parameters["menu_id"]) {
				$get_items_query .= "
				AND     menu_id = ?";
				array_push($bind_params,$parameters["menu_id"]);
			}
			if (isset($parameters['parent_id'])) {
				$get_items_query .= "
				AND		parent_id = ?";
				array_push($bind_params,$parameters['parent_id']);
			}

			$get_items_query .= "
				ORDER BY view_order,title
			";
			#query_log($get_items_query);
			$rs = $GLOBALS['_database']->Execute($get_items_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$items = array();
			while(list($id) = $rs->FetchRow()) {
				$this->incrementCount();
				$item = new Item($id);
				array_push($items,$item);
			}
			return $items;
		}
	}
