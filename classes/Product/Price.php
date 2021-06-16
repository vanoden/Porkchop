<?php
	namespace Product;

	class Price {
		public $id;
		public $product_id;
		public $amount;
		public $status;
		public $currency_id;
		public $date_active;

		public function __construct($id = 0) {
			if ($id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function getCurrent($product_id) {
			$get_price_query = "
				SELECT	amount
				FROM	product_prices
				WHERE	product_id = ?
				AND		status = 'ACTIVE'
				AND		date_active <= sysdate()
				ORDER BY date_active DESC
				LIMIT 1
			";
			$rs = $GLOBALS['_database']->Execute($get_price_query,array($product_id));
			list($price) = $rs->FetchRow();
			return $price;
		}

		public function details() {
			$get_detail_query = "
				SELECT	amount,
						currency_id,
						date_active,
						status
				FROM	product_prices
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_detail_query,array($this->id));
			$object = $rs->FetchNextObject();
			if (isset($object->amount)) {
				$this->currency_id = $object->currency_id;
				$this->amount = $object->amount;
				$this->currency_id = $object->currency_id;
				$this->date_active = $object->date_active;
				$this->status = $object->status;
				return true;
			}
			else {
				$this->id = null;
				$this->currency_id = null;
				$this->amount = null;
				$this->product_id = null;
				$this->date_active = null;
				$this->status = null;
				return false;
			}
		}
	}
?>
