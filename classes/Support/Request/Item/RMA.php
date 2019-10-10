<?
	namespace Support\Request\Item;

	class RMA {
		private $_error;
		public $code;
		public $approvedBy;
		public $date_approved;
		public $item;
		private $item_id;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				return $this->details();
			}
		}

		public function add($parameters) {
			$approvedBy = new \Register\Customer($parameters['approved_id']);
			if ($approvedBy->error) {
				$this->_error = $approvedBy->error;
				return false;
			}
			if (! $approvedBy->id) {
				$this->_error = "Approver not found";
				return false;
			}

			if (isset($parameters['date_approved'])) {
				if (get_mysql_date($parameters['date_approved'])) {
					$date_approved = get_mysql_date($parameters['date_approved']);
				}
				else {
					$this->_error = "Invalid date for approval";
					return false;
				}
			}
			else {
				$date_approved = date('Y-m-d H:i:s');
			}
			
			$item = new \Support\Request\Item($parameters['item_id']);
			if ($item->error()) {
				$this->_error = $item->error();
				return false;
			}
			if (! $item->id) {
				$this->_error = "Request Item not found";
				return false;
			}

			if (isset($parameters['code'])) {
				$check = new \Support\Request\RMA();
				if ($check->get($parameters['code'])) {
					$this->_error = "Code already used";
					return false;
				}
				else $code = $parameters['code'];
			}
			else {
				$code = uniqid();
			}
			$add_object_query = "
				INSERT
				INTO	support_rmas
				(		code,
						item_id,
						approved_id,
						date_approved,
						status
				)
				VALUES
				(		?,?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_object_query,
				array(
					$code,
					$item->id,
					$approvedBy->id,
					$date_approved,
					'NEW'
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Support::Request::RMA::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters) {
			return $this->details();
		}

		public function get($code) {
			$get_object_query = "
				SELECT	*
				FROM	support_rmas
				WHERE	code = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,array($code)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::RMA::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details();
		}

		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	support_rmas
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,array($this->id)
			);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::RMA::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			$this->id = $object->id;
			$this->code = $object->code;
			$this->date_approved = $object->date_approved;
			$this->approvedBy = new \Register\Customer($object->approved_id);
			$this->status = $object->status;
			$this->shipment = new \Shipping\Shipment($object->shipment_id);
			$this->item_id = $object->item_id;
			$this->document_id = $object->document_id;
			return true;
		}

		public function document() {
			return new \Storage\File($this->document_id);
		}

		public function item() {
			return new \Support\Request\Item($this->item_id);
		}
		public function number() {
			return sprintf("RMA%05d",$this->id);
		}
		public function events() {
			return null;
		}

		public function exists() {
			if (is_numeric($this->id)) return true;
			return false;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
