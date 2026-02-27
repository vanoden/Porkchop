<?php
	namespace Site\Navigation;

	class ItemList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\Navigation\Item';
		}

		public function findAdvanced($parameters,$advanced,$controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build the Query
			$get_items_query = "
				SELECT  id
				FROM    navigation_menu_items
				WHERE   id = id
			";

			// Add Parameters
			if (!empty($parameters["menu_id"])) {
				$get_items_query .= "
				AND     menu_id = ?";
				$database->AddParam($parameters["menu_id"]);
			}
			if (isset($parameters['parent_id'])) {
				$get_items_query .= "
				AND		parent_id = ?";
				$database->AddParam($parameters['parent_id']);
			}
			if (!empty($parameters['target'])) {
				$get_items_query .= "
				AND		target = ?";
				$database->AddParam($parameters['target']);
			}

			// Order Clause
			$get_items_query .= "
				ORDER BY view_order,title
			";

			$rs = $database->Execute($get_items_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			$items = array();
			while(list($id) = $rs->FetchRow()) {
				$item = new Item($id);
				if ($item->required_role_id > 0) {
					if (empty($GLOBALS['_SESSION_']->customer)) continue;
					if (!$GLOBALS['_SESSION_']->customer->can("manage navigation menus") && !$GLOBALS['_SESSION_']->customer->has_role_id($item->required_role_id)) continue;
				}
				if ($item->required_product_id > 0) {
					if (empty($GLOBALS['_SESSION_']->customer)) continue;
					if (!$GLOBALS['_SESSION_']->customer->can("manage navigation menus") && !$GLOBALS['_SESSION_']->customer->hasProductID($item->required_product_id)) continue;
				}
				$this->incrementCount();
				array_push($items,$item);
			}
			return $items;
		}
	}
