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

			if (!empty($parameters["menu_id"])) {
				$get_items_query .= "
				AND     menu_id = ?";
				array_push($bind_params,$parameters["menu_id"]);
			}
			if (isset($parameters['parent_id'])) {
				$get_items_query .= "
				AND		parent_id = ?";
				array_push($bind_params,$parameters['parent_id']);
			}
			if (!empty($parameters['target'])) {
				$get_items_query .= "
				AND		target = ?";
				array_push($bind_params,$parameters['target']);
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
				$item = new Item($id);
				if (!$GLOBALS['_SESSION_']->customer->can("manage navigation menus") && $item->required_role_id > 0) {
					if (!$GLOBALS['_SESSION_']->customer->has_role_id($item->required_role_id)) continue;
				}
				$this->incrementCount();
				array_push($items,$item);
			}
			return $items;
		}
	}
