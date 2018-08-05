<?php
	namespace Issue;
	
	class EventList {
		private $_count;
		private $_error;
		
		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM
			";

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in Issue::ProductList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new Event($id);
				array_push($objects,$object);
			}
			return $objects;
		}

		public function error() {
			return $this->_error;
		}
	}
?>