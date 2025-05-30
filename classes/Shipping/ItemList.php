<?php
	namespace Shipping;

	class ItemList Extends \BaseListClass {

		public function __construct() {
			$this->_modelName = '\Shipping\Item';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	id, product_id
				FROM	shipping_items
				WHERE	id = id";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (!empty($parameters['shipment_id']) && is_numeric($parameters['shipment_id'])) {
				$shipment = new Shipment($parameters['shipment_id']);
				if ($shipment->exists()) {
					$find_objects_query .= "
						AND		shipment_id = ?
					";
					$database->AddParam($parameters['shipment_id']);
				}
				else {
					$this->error('Shipment not found');
					return [];
				}
			}

			if (!empty($parameters['package_id']) && is_numeric($parameters['package_id'])) {
				$package = new \Package\Package($parameters['package_id']);
				if ($package->exists()) {
					$find_objects_query .= "
						AND		package_id = ?
					";
					$database->AddParam($parameters['package_id']);
				}
				else {
					$this->error('Package not found');
					return [];
				}
			}

			if (!empty($parameters['serial_number'])) {
				if ($validationClass->validCode($parameters['serial_number'])) {
					$find_objects_query .= "
						AND		serial_number = ?
					";
					$database->AddParam($parameters['serial_number']);
				}
				else {
					$this->error('Invalid serial number');
					return [];
				}
			}

			if (!empty($parameters['product_id']) && is_numeric($parameters['product_id'])) {
				$product = new \Product\Item($parameters['product_id']);
				if ($product->exists()) {
					$find_objects_query .= "
						AND		product_id = ?
					";
					$database->AddParam($parameters['product_id']);
					$product_id = $product->id;
				}
				else {
					$this->error('Product not found');
					return [];
				}
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return [];
			}

			// Build Results
			$objects = array();
			while (list($shipping_item_id,$product_id) = $rs->FetchRow()) {
				if (empty($shipping_item_id)) {
					app_log("Shipping Item ID is empty for shipping item ID: $shipping_item_id");
					continue;
				}
				$shipping_item = new \Shipping\Item($shipping_item_id);
				$object = $shipping_item;
				if ($this->error()) {
					$this->error("Error getting details for ".$this->_modelName.": ".$this->error());
					return [];
				}
				array_push($objects,$object);
			}

			return $objects;
		}
	}
