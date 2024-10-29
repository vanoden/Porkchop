<?php
	namespace Network;

	class InterfaceList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Network\Interface';
		}

		public function findAdvanced($parameters,$advanced,$controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_list_query = "
				SELECT	id
				FROM	network_interfaces
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();
			if (isset($parameters['host_id']) && is_numeric($parameters['host_id']) && $parameters['host_id'] > 0) {
				$get_list_query .= "
				AND	host_id = ?";
				$database->AddParam($parameters['host_id']);
			}
			if (isset($parameters['name']) && strlen($parameters['name']) > 0 && $validationClass->validName($parameters['name'])) {
				$get_list_query .= "
				AND		name = ?";
				$database->AddParam($parameters['name']);
			}
			if (isset($parameters['type']) && strlen($parameters['type']) > 0 && $validationClass->validType($parameters['type'])) {
				$get_list_query .= "
				AND		type = ?";
				$database->AddParam($parameters['type']);
			}
			if (isset($parameters['mac_address']) && strlen($parameters['mac_address']) > 0 && $validationClass->validMacAddress($parameters['mac_address'])) {
				$get_list_query .= "
				AND		mac_address = ?";
				$database->AddParam($parameters['mac_address']);
			}

			// Order Clause
			$get_list_query .= "
				ORDER BY name";

			// Execute Query
			$rs = $database->Execute($get_list_query);

			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
