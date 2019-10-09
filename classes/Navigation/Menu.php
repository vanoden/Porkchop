<?php
	namespace Navigation;

	class Menu {
		public $id;
		public $name;
		public $error;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	navigation_menus
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Navigation::Menu::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($id) = $rs->FetchRow();
			if (isset($id)) {
				$this->id = $id;
				return $this->details();
			}
			else {
				$this->_error = "Menu not found";
				return false;
			}
		}

		public function add($parameters = array()) {
			if (! isset($parameters['code'])) {
				$this->_error = "code required";
				return false;
			}
			$add_object_query = "
				INSERT
				INTO	navigation_menus
				(code)
				VALUES
				(?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array($parameters['code'])
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = $GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}
		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	navigation_menus
				SET		id = id
			";
			$bind_params = array();

			if (isset($parameters['code'])) {
				$update_object_query .= ",
						code = ?";
				array_push($bind_params,$parameters['code']);
			}
			if (isset($parameters['title'])) {
				$update_object_query .= ",
						title = ?";
				array_push($bind_params,$parameters['title']);
			}
			$update_object_query .= "
				WHERE	id = ?
			";
			array_push($bind_params,$this->id);
			query_log($update_object_query);
			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Navigation::Menu::update(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return $this->details();
		}
		public function details() {
			$get_default_query = "
				SELECT  id,code,title
				FROM    navigation_menus
				WHERE   id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_default_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in Navigation::Menu::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);

			if ($object->id) {
				$this->id = $object->id;
				$this->code = $object->code;
				$this->title = $object->title;
			}
			else {
				$this->id = null;
				$this->code = null;
				$this->title = null;
			}
			return true;
		}

		public function items($parent_id=0) {
			if (! preg_match("/^\d+$/",$parent_id)) $parent_id = 0;

			$itemlist = new \Navigation\ItemList();
			$items = $itemlist->find(array('menu_id' => $this->id,'parent_id' => $parent_id));
			if ($itemlist->error) {
				$this->_error = $itemlist->error;
				return null;
			}
			return $items;
		}

		public function cascade($parent_id = 0) {
			$response = array();
			$items = $this->items($parent_id);
			foreach ($items as $item) {
				$item->item = $this->cascade($item->id);
				array_push($response,$item);
			}
			return $response;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
