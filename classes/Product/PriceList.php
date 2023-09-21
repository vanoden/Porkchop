<?php
namespace Product;

class PriceList Extends \BaseListClass {
	public function find($parameters = null) {
		$bind_params = array();

		$get_prices_query = "
			SELECT	id
			FROM	product_prices
			WHERE	id = id";

		if (isset($parameters["product_id"])) {
			$get_prices_query .= "
			AND		product_id = ?";
			array_push($bind_params,$parameters['product_id']);
		}
		if (isset($parameters["date_price"])) {
			$date = get_mysql_date($parameters['date_price']);
			$get_prices_query .= "
			AND		date_active <= ?";
			array_push($bind_params,$date);
		}
		if (isset($parameters["status"])) {
			$get_prices_query .= "
			AND		status = ?
			";
			array_push($bind_params,$parameters['status']);
		}

		$get_prices_query .= "
			ORDER BY date_active DESC, status";

		query_log($get_prices_query);

		$rs = $GLOBALS['_database']->Execute($get_prices_query,$bind_params);

		if ($GLOBALS['_database']->ErrorMsg()) {
			$this->SQLError($GLOBALS['_database']->ErrorMsg());
			return null;
		}

		$prices = array();
		while (list($id) = $rs->FetchRow()) {
			$price = new Price($id);
			$this->incrementCount();
			array_push($prices,$price);
		}

		return $prices;
	}
}
