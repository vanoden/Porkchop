<?
	namespace Shipping;
	
	class Shipment {
		private $_error;
		public $id;
		public $vendor;
		public $date_shipped;
		public $date_received;
		
		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			$bind_params = array();
			$add_object_query = "
			";

			$GLOBALS["_database"]->Execute($add_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Shipping::Vendor::add() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			$this->id = $GLOBAL['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			$bind_params = array();
			$update_object_query = "
			";

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS["_database"]->Execute($update_object_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Shipping::Vendor::update() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}

			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($code));
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::Vendor::get() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	<TABLE>
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Shipping::Vendor::details() ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			return true;
		}

		public function error() {
			return $this->_error;
		}
?>