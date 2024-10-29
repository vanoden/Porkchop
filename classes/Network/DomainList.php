<?php
	namespace Network;

	class DomainList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Network\Domain';
		}

		public function findAdvanced($parameters,$advanced,$controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_list_query = "
				SELECT	id
				FROM	network_domains
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();
			if (isset($parameters['name']) && strlen($parameters['name']) && $validationClass->validName($parameters['name'])) {
				$get_list_query .= "
				AND		name = ?";
				$database->AddParam($parameters['name']);
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
				$object = new Domain($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
