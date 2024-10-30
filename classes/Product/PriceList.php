<?php
namespace Product;

class PriceList Extends \BaseListClass {
	public function __construct() {
		$this->_modelName = '\Product\Price';
	}

	public function findAdvanced($parameters, $advanced, $controls): array {
		$this->clearError();
		$this->resetCount();

		// Initialize Database Service
		$database = new \Database\Service();

		// Build Query
		$get_objects_query = "
			SELECT	id
			FROM	product_prices
			WHERE	id = id";

		// Add Parameters
		$validationClass = new $this->_modelName();
		if (isset($parameters["product_id"]) && is_numeric($parameters["product_id"])) {
			$product = new \Product\Item($parameters['product_id']);
			if ($product->exists()) {
				$get_objects_query .= "
				AND		product_id = ?
				";
				$database->AddParam($parameters['product_id']);
			}
			else {
				$this->error("Product not found");
				return [];
			}
		}
		if (isset($parameters["date_price"])) {
			if ($validationClass->validDate($parameters["date_price"])) {
				$date = get_mysql_date($parameters['date_price']);
				$get_objects_query .= "
				AND		date_active <= ?";
				$database->AddParam($date);
			}
			else {
				$this->error("Invalid date");
				return [];
			}
		}
		if (isset($parameters["status"]) && !empty($parameters["status"])) {
			if ($validationClass->validStatus($parameters["status"])) {
				$get_objects_query .= "
				AND		status = ?
				";
				$database->AddParam($parameters['status']);
			}
			else {
				$this->error("Invalid status");
				return [];
			}
		}

		$get_objects_query .= "
			ORDER BY date_active DESC, status";

		// Limit Clause
		$get_objects_query .= $this->limitClause($controls);

		// Execute Query
		$rs = $database->Execute($get_objects_query);

		if ($database->ErrorMsg()) {
			$this->SQLError($database->ErrorMsg());
			return [];
		}

		$objects = array();
		while (list($id) = $rs->FetchRow()) {
			$object = new $this->_modelName($id);
			$this->incrementCount();
			array_push($objects,$object);
		}

		return $objects;
	}
}
