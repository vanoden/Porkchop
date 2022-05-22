<?php
	namespace Sales;

	class CurrencyList {
		public $_count;
		public $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	sales_currencies
				WHERE	id = id
			";

			$bind_params = array();

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->error("SQL Error in Sales::CurrencyList::find(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new \Sales\Currency($id);
				array_push($objects,$object);
				$this->_count ++;
			}
			return $objects;
		}

		public function error($message = null) {
			if (!empty($message)) $this->_error = $message;
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
?>
