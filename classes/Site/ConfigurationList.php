<?php
	namespace Site;
		
	class ConfigurationList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\Configuration';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	`key`
				FROM	site_configurations
				WHERE	`key` = `key`
			";
			
			if (!empty($parameters['key'])) {
				if ($workingClass->validCode($parameters['key'])) {
					$find_objects_query .= "
					AND `key` = ?";
					$database->AddParam($parameters['key']);
				}
				else {
					$this->error("Invalid key");
					return [];
				}
			}

			if (!empty($parameters['value'])) {
				if ($workingClass->validateValue($parameters['value'])) {
					$find_objects_query .= "
					AND `value` = ?";
					$database->AddParam($parameters['value']);
				}
				else {
					$this->error("Invalid value");
					return [];
				}
			}

			// Order Clause
			$find_objects_query .= "
					ORDER BY `key`
			";
	
			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			// Build Results
			$objects = [];
			while(list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				$this->incrementCount();
				array_push($objects,$object);
			}
			return $objects;
		}
	}
