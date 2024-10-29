<?php
	namespace Network;

	class IPAddressList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Network\IPAddress';
		}

		public function findAddress($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_list_query = "
				SELECT	id
				FROM	network_addresses
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();
			if (isset($parameters['adapter_id']) && is_numeric($parameters['adapter_id']) && $parameters['adapter_id'] > 0) {
				$get_list_query .= "
				AND	adapter_id = ?";
				$database->AddParam($parameters['adapter_id']);
			}
			if (isset($parameters['type']) && strlen($parameters['type']) > 0 && $validationClass->validateType($parameters['type'])) {
				$get_list_query .= "
				AND		type = ?";
				$database->AddParam($parameters['type']);
			}

			// Order Clause
			$get_list_query .= "
				ORDER BY adapter_id,type";

			// Execute Query
			$rs = $database->Execute($get_list_query);

			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new IPAddress($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
