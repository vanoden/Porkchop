<?php
	namespace Navigation;

	class Item Extends \BaseClass {

		private $menu_id;
		public $title;
		public $target;
		public $view_order;
		public $alt;
		public $description;
		public $parent_id;
		public $external = false;
		public $ssl = false;

		public function __construct($id = null) {
			$this->_tableName = 'navigation_menu_items';
			$this->_tableUKColumn = null;

			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function __call($name, $arguments) {
			if ($name == "get") return $this->getItem($arguments[0],$arguments[1],$arguments[2]);
			else $this->error("Method '$name' not found");
		}

		public function add($parameters) {
			if ($parameters['menu_id']) {
				$menu = new Menu($parameters['menu_id']);
				if (! $menu->id) {
					$this->error("Menu not found");
					return false;
				}
			}

			$add_object_query = "
				INSERT
				INTO	navigation_menu_items
				(		menu_id,title)
				VALUES
				(		?,?)
			";

			$bind_params = array($parameters['menu_id'],$parameters['title']);
			$GLOBALS['_database']->Execute($add_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$id = $GLOBALS['_database']->Insert_ID();
			$this->id = $id;
			return $this->update($parameters);
		}

		public function getItem($menu_id,$code,$parent = null): bool {
			$get_object_query = "
				SELECT	id
				FROM	navigation_menu_items
				WHERE	menu_id = ?
				AND		title = ?
			";

			$bind_params = array($menu_id,$code);
			if (isset($parent) and is_object($parent)) {
				$get_object_query .= "
				AND		parent_id = ?";
				array_push($bind_params,$parent->id);
			}

            query_log($get_object_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_object_query,$bind_params);

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
				return $this->details();
			}
			else {
				return false;
			}
		}

		public function update($parameters): bool {
			$update_object_query = "
				UPDATE	navigation_menu_items
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['title'])) {
				$update_object_query .= ",
						title = ?";
				array_push($bind_params,$parameters['title']);
			}
			if (isset($parameters['target'])) {
				$update_object_query .= ",
						target = ?";
				array_push($bind_params,$parameters['target']);
			}
			if (isset($parameters['parent_id'])) {
				$update_object_query .= ",
					parent_id = ?";
				array_push($bind_params,$parameters['parent_id']);
			}
			if (isset($parameters['alt'])) {
				$update_object_query .= ",
						alt = ?";
				array_push($bind_params,$parameters['alt']);
			}
			if (isset($parameters['description'])) {
				$update_object_query .= ",
						description = ?";
				array_push($bind_params,$parameters['description']);
			}
			if (isset($parameters['view_order'])) {
				$update_object_query .= ",
						view_order = ?";
				array_push($bind_params,$parameters['view_order']);
			}

			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);
			query_log($update_object_query,$bind_params,true);
			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return $this->details();
		}

		public function details(): bool {
			$get_object_query = "
				SELECT	*
				FROM	navigation_menu_items
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->menu_id= $object->menu_id;
				$this->title = $object->title;
				$this->target = $object->target;
				$this->view_order = $object->view_order;
				$this->alt = $object->alt;
				$this->description = $object->description;
				$this->parent_id = $object->parent_id;
				if ($object->external) $this->external = true;
				else $this->external = false;
				if ($object->ssl) $this->ssl = true;
				else $this->ssl = false;
			}
			else {
				$this->id = null;
				$this->menu_id = null;
				$this->title = null;
				$this->target = null;
				$this->view_order = null;
				$this->alt = null;
				$this->description = null;
				$this->parent_id = null;
				$this->external = null;
				$this->ssl = null;
			}
			return true;
		}

		public function menu() {
			return new \Navigation\Menu($this->menu_id);
		}

		public function hasChildren() {
			$get_children_query = "
				SELECT	count(*)
				FROM	navigation_menu_items
				WHERE	parent_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_children_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($count) = $rs->FetchRow();
			if ($count > 0) {
				return true;
			}
			else {
				return false;
			}
		}

		public function children() {
			$itemList = new \Navigation\ItemList();
			$items = $itemList->find(array('parent_id' => $this->id));
			return $items;
		}

		public function validTitle($string) {
			return $this->validName($string);
		}

		public function validTarget($string) {
			if (preg_match('/\.\./',$string)) return false;
			if (preg_match('/\<\>/',$string)) return false;
			return true;
		}
	}