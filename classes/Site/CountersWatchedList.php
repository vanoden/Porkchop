<?php
	namespace Site;

	class CountersWatchedList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\CounterWatched';
		}

		public function findAdvanced(array $parameters, array $advanced, array $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	*
				FROM	counters_watched 
				WHERE	`key` not like '%[%]'
			";			

			// Add Parameters
			if (!empty($parameters['key'])) {
				if ($workingClass->validCode($parameters['key'])) {
					$find_objects_query .= "
					AND = ?";
					$database->AddParam($parameters['key']);
				}
				else {
					$this->error("Invalid key");
					return [];
				}
			}
			if (!empty($parameters['notes'])) {
				$find_objects_query .= "
				AND notes = ?";
				$database->AddParam($parameters['notes']);
			}
	
			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
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
