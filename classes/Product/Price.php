<?php
	namespace Product;

	class Price Extends \BaseClass {

		public $product_id;
		public $amount;
		public $status;
		public $currency_id;
		public $date_active;

		public function __construct($id = 0) {
			$this->_tableName = 'product_prices';
			$this->_addStatus(array('ACTIVE','INACTIVE'));
    		parent::__construct($id);
		}

		public function add($parameters = []) {
			$product = new \Product\Item($parameters['product_id']);
			if (!$product->id) {
				$this->error("Product not found");
				return false;
			}

			if (!empty($parameters['currency_code'])) {
				$currency = new \Sales\Currency();
				if (! $currency->get($parameters['currency_code'])) {
					$this->error("currency not found");
					return false;
				}
			}
			elseif (!empty($parameters['currency_id'])) {
				$currency = new \Sales\Currency($parameters['currency_id']);
				if (! $currency->id) {
					$this->error("currency not found");
					return false;
				}
			}
			else {
				if (empty($GLOBALS['_config']->sales) || empty($GLOBALS['_config']->sales->default_currency)) {
					$this->error("No default currency set");
					return false;
				}
				$currency = new \Sales\Currency();
				if (! $currency->get($GLOBALS['_config']->sales->default_currency)) {
					if (! $currency->add(array('name' => $GLOBALS['_config']->sales->default_currency))) {
						$this->error("Unable to add default currency");
						return false;
					}
				}
			}

			if (empty($parameters['date_active'])) {
				$parameters['date_active'] = get_mysql_date(time());
			}
			elseif (get_mysql_date($parameters['date_active'])) {
				$parameters['date_active'] = get_mysql_date($parameters['date_active']);
			}
			else {
				$this->error("Invalid active date");
				return false;
			}

			if (isset($parameters['status']) && preg_match('/^(ACTIVE|INACTIVE)$/',$parameters['status'])) {
				# All Set
			}
			elseif(isset($parameters['status'])) {
				$this->error("Invalid status");
				return false;
			}
			else {
				$parameters['status'] = 'INACTIVE';
			}

			$insert_price_query = "
				INSERT
				INTO	product_prices
				(		product_id,
						amount,
						date_active,
						status,
						currency_id
				)
				VALUES
				(		?,?,?,?,?)
			";
			$GLOBALS['_database']->Execute(
				$insert_price_query,
				array(
					$product->id,
					$parameters['amount'],
					$parameters['date_active'],
					$parameters['status'],
					$currency->id
				)
			);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			app_log("User ".$GLOBALS['_SESSION_']->customer->id." added price '".$this->id."'");
			return $this->details();
		}

		public function getCurrent($product_id) {
			$get_price_query = "
				SELECT	id
				FROM	product_prices
				WHERE	product_id = ?
				AND		status = 'ACTIVE'
				AND		date_active <= sysdate()
				ORDER BY date_active DESC
				LIMIT 1
			";
			query_log($get_price_query,array($product_id),true);
			$rs = $GLOBALS['_database']->Execute($get_price_query,array($product_id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			list($id) = $rs->FetchRow();
			if (! $id) {
				$this->error("Price not set for product $product_id");
				return null;
			}
			$this->id = $id;
			return $this->details();
		}

		public function details(): bool {
			$get_detail_query = "
				SELECT	amount,
						currency_id,
						date_active,
						status
				FROM	product_prices
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_detail_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}

			$object = $rs->FetchNextObject(false);
			if (isset($object->amount)) {
				$this->currency_id = $object->currency_id;
				$this->amount = $object->amount;
				$this->currency_id = $object->currency_id;
				$this->date_active = $object->date_active;
				$this->status = $object->status;
			}
			else {
				$this->id = null;
				$this->currency_id = null;
				$this->amount = null;
				$this->product_id = null;
				$this->date_active = null;
				$this->status = null;
			}
			return true;
		}
	}
?>
