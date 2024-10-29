<?php
namespace Product;

class PriceAuditList Extends \BaseListClass {
	public function __construct() {
		$this->_modelName = '\Product\PriceAudit';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Build Query
		$get_price_audit_query = "
			SELECT	ppa.id
			FROM	product_prices_audit ppa
			INNER   JOIN product_prices pp ON pp.id = ppa.product_price_id
			WHERE	ppa.id = ppa.id";

		// Add Parameters
		$validationClass = new $this->_modelName();
		if (isset($parameters["date_updated"])) {
			$date = get_mysql_date($parameters['date_updated']);
			$get_price_audit_query .= "
			AND		ppa.date_updated <= ?";
			$database->AddParams($date);
		}
		
		if (isset($parameters["product_id"]) && is_numeric($parameters["product_id"])) {
			$product = new \Product\Item($parameters['product_id']);
			if ($product->exists()) {
				$get_price_audit_query .= "
				AND		pp.product_id = ?
				";
				$database->AddParam($parameters['product_id']);
			}
			else {
				$this->error("Product not found");
				return [];
			}
		}

		if (isset($parameters["user_id"]) && is_numeric($parameters["user_id"])) {
			$user = new \Register\Customer($parameters['user_id']);
			if (!$user->id) {
                $this->error("User not found");
                return [];
			}
			$get_price_audit_query .= "
			AND		ppa.user_id = ?
			";
			$database->AddParam($parameters['user_id']);
		}

		if (isset($parameters["product_price_id"]) && is_numeric($parameters["product_price_id"])) {
			$product_price = new \Product\Price($parameters['product_price_id']);
			if (!$product_price->exists()) {
				$this->error("Product Price not found");
				return [];
			}
			$get_price_audit_query .= "
			AND		ppa.product_price_id = ?";
			$database->AddParam($parameters['product_price_id']);
		}

		$get_price_audit_query .= "
			ORDER BY ppa.date_updated DESC";

		// Limit Clause
		$get_price_audit_query .= $this->limitClause($controls);
		
		$rs = $database->Execute($get_price_audit_query);

		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		$priceAudits = array();
		while (list($id) = $rs->FetchRow()) {
			$priceAudit = new PriceAudit($id);
			$this->incrementCount();
			array_push($priceAudits,$priceAudit);
		}
	
		return $priceAudits;
	}
}
