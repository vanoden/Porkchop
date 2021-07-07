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
			return $this->update($parameters);
		}

		public function update($parameters) {
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
				$this->_error = "Error getting currency: ".$GLOBALS["_database"]->ErrorMsg();
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
	}
?>
