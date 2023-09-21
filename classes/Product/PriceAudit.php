<?php
namespace Product;

class PriceAudit Extends \BaseModel {

    public $id;
	public $product_price_id;
	public $user_id;
	public $date_updated;
	public $note;

	public function __construct($id = 0) {
		$this->_tableName = 'product_prices_audit';
		parent::__construct($id);
	}

	public function add($parameters = []) {
	
		$productPrice = new \Product\Price($parameters['product_price_id']);
		if (!$productPrice->id) {
			$this->error("Product Price not found");
			return false;
		}

		if (empty($parameters['date_updated'])) {
			$parameters['date_updated'] = get_mysql_date(time());
		} elseif (get_mysql_date($parameters['date_updated'])) {
			$parameters['date_updated'] = get_mysql_date($parameters['date_updated']);
		} else {
			$this->error("Invalid updated date");
			return false;
		}
		
		if (empty($parameters['note'])) $parameters['note'] = "";
		
	    // check valid user
		if (isset($parameters['user_id'])) {
			$user = new \Register\Customer($parameters['user_id']);
			if (!$user->id) {
                $this->error("User not found");
                return false;
			}
		} elseif (empty($parameters['user_id'])) {
            $this->error("User not found");
            return false;		    
		}

		$insert_price_query = "
			INSERT
			INTO	product_prices_audit
			(		product_price_id,
					user_id,
					date_updated,
					note
			)
			VALUES
			(		?,?,?,?)
		";
		
		$GLOBALS['_database']->Execute(
			$insert_price_query,
			array(
				$productPrice->id,
				$parameters['user_id'],
				$parameters['date_updated'],
				$parameters['note']
			)
		);
		
		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return false;
		}
		$this->id = $GLOBALS['_database']->Insert_ID();
		app_log("User ".$GLOBALS['_SESSION_']->customer->id." new price has been audited '".$this->id."'");
		return $this->details();
	}

	public function details(): bool {
	
		$get_detail_query = "
			SELECT	id, 
			        product_price_id,
					user_id,
					date_updated,
					note
			FROM	product_prices_audit
			WHERE	id = ?
		";
		
		$rs = $GLOBALS['_database']->Execute($get_detail_query,array($this->id));
		if (! $rs) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return false;
		}

		$object = $rs->FetchNextObject(false);
		if (isset($object->id)) {
			$this->product_price_id = $object->product_price_id;
			$this->user_id = $object->user_id;
			$this->date_updated = $object->date_updated;
			$this->note = $object->note;
		} else {
			$this->id = null;
			$this->product_price_id = null;
			$this->user_id = null;
			$this->date_updated = null;
			$this->note = null;
		}
		return true;
	}
}
