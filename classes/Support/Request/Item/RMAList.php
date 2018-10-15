<?
	namespace Support\Request\Item;
	
	class RMAList {
		private $_error;
		public $count;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	support_rmas
				WHERE	id = id
			";
			$bind_params = array();
			if (isset($parameters['item_id'])) {
				$item = new \Support\Request\Item($parameters['item_id']);
				if ($item->error()) {
					$this->_error = $item->error();
					return false;
				}
				if (! $item->id) {
					$this->_error = "Item not found";
					return false;
				}
				$find_objects_query .= "
				AND		item_id = ?
				";
				array_push($bind_params,$item->id);
			}
			
			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::RMAList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new \Support\Request\Item\RMA($id);
				array_push($objects,$object);
				$this->count ++;
			}
			return $objects;
		}
	}
?>
