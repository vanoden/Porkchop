<?php
	namespace Sales;

	class Currency {
		public $id;
		public $name;
		public $symbol;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			if (empty($parameters['name'])) {
				$this->error("Currency name required");
				return false;
			}
			$add_currency_query = "
				INSERT
				INTO	sales_currencies
						(name)
				VALUE	(?)
			";
			$GLOBALS['_database']->Execute($add_currency_query,array($parameters['name']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error("SQL Error in Sales::Current::add(): ".$GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($id) = $GLOBALS['_database']->Insert_ID();
			$this->id = $id;
			return $this->update($parameters);
		}

		public function update($parameters) {
			return $this->details();
		}

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	sales_currencies
				WHERE	name = ?";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($name));
			if (! $rs) {
				$this->error("SQL Error in Sales::Currency::get(): ".$GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list ($id) = $rs->FetchRow();
			if (empty($id)) {
				$this->error("Currency not found");
				return false;
			}
			$this->id = $id;
			return $this->details();
		}
		public function details() {
			$get_details_query = "
				SELECT	id,
						name,
						symbol
				FROM	sales_currencies
				WHERE	id = ?
			";

			$rs = $GLOBALS["_database"]->Execute($get_details_query,array($this->id));
			if (! $rs) {
				$this->error("Error getting currency: ".$GLOBALS["_database"]->ErrorMsg());
				return false;
			}
			else {
				$object = $rs->FetchNextObject(false);
				$this->id = $object->id;
				$this->name = $object->name;
				$this->symbol = $object->symbol;
				return true;
			}
		}
		public function error($message = null) {
			if (isset($message)) $this->_error = $message;
			return $this->_error;
		}
	}
?>
