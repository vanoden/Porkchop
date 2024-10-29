<?php
	namespace Network;

	class HostList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Network\Host';
		}

		public function findAdvanced($parameters,$advanced,$controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_list_query = "
				SELECT	id
				FROM	network_hosts
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();
			if (isset($parameters['domain_id']) && $parameters['domain_id'] > 0) {
				$get_list_query .= "
				AND	domain_id = ?";
				$database->AddParam($parameters['domain_id']);
			}
			if (isset($parameters['name']) && strlen($parameters['name']) && $validationClass->validName($parameters['name'])) {
				$get_list_query .= "
				AND		name = ?";
				$this->AddParam($parameters['name']);
			}
			if (isset($parameters['os_name']) && strlen($parameters['os_name']) && $validationClass->validName($parameters['os_name'])) {
				$get_list_query .= "
				AND		os_name = ?";
				$this->AddParam($parameters['os_name']);
			}
			if (isset($parameters['os_version']) && strlen($parameters['os_version']) && $validationClass->validName($parameters['version'])) {
				$get_list_query .= "
				AND		os_version = ?";
				$this->AddParam($parameters['os_version']);
			}

			// Order Clause
			$get_list_query .= "
				ORDER BY name";

			// Execute Query
			$rs = $GLOBALS['_database']->Execute($get_list_query);

			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new Host($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
