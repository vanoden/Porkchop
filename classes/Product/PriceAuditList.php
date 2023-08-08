<?php
namespace Product;

class PriceAuditList Extends \BaseListClass {

	public function find($parameters = null) {
		$bind_params = array();

		$get_price_audit_query = "
			SELECT	ppa.id
			FROM	product_prices_audit ppa
			INNER   JOIN product_prices pp ON pp.id = ppa.product_price_id
			WHERE	ppa.id = ppa.id";

		if (isset($parameters["date_updated"])) {
			$date = get_mysql_date($parameters['date_updated']);
			$get_price_audit_query .= "
			AND		ppa.date_updated <= ?";
			array_push($bind_params,$date);
		}
		
		if (isset($parameters["product_id"])) {
			$get_price_audit_query .= "
			AND		pp.product_id = ?
			";
			array_push($bind_params,$parameters['product_id']);
		}

		if (isset($parameters["user_id"])) {
			$user = new \Register\Customer($parameters['user_id']);
			if (!$user->id) {
                $this->error("User not found");
                return false;
			}
			$get_price_audit_query .= "
			AND		ppa.user_id = ?
			";
			array_push($bind_params,$parameters['user_id']);
		}

		if (isset($parameters["product_price_id"])) {
			$get_price_audit_query .= "
			AND		ppa.product_price_id = ?";
			array_push($bind_params,$parameters['product_price_id']);
		}

		$get_price_audit_query .= "
			ORDER BY ppa.date_updated DESC";
		query_log($get_price_audit_query);

		$rs = $GLOBALS['_database']->Execute($get_price_audit_query,$bind_params);

		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
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
