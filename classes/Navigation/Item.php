<?php
	namespace Navigation;

	class Item {
		private $_error;
		public $id;
		public $menu;
		public $title;
		public $target;
		public $view_order;
		public $alt;
		public $description;
		public $parent_id;
		public $external = false;
		public $ssl = false;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			if ($parameters['menu_id']) {
				$menu = new Menu($parameters['menu_id']);
				if (! $menu->id) {
					$this->_error = "Menu not found";
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
				$this->_error = "SQL Error in Navigation::Item::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			list($id) = $GLOBALS['_database']->Insert_ID();
			$this->id = $id;
			return $this->update($parameters);
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	navigation_menu_items
				WHERE	code = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($code));

			if (! $rs) {
				$this->_error = "SQL Error in Navigation::Item::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($id) = $rs->FetchRow();
			if ($id) {
				$this->id = $id;
				return $this->details();
			}
			else {
				$this->_error = "Item not found";
				return false;
			}
		}

		public function update($parameters) {
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

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Navigation::Item::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function details() {
			app_log("Get details for menu item ".$this->id,'trace');
			$get_object_query = "
				SELECT	*
				FROM	navigation_menu_items
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Navigation::Item::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->menu = new Menu($object->menu_id);
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
				$this->menu = new Menu(null);
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

		public function error() {
			return $this->_error;
		}
	}
?>