<?php
	namespace Calendar;

	class EventList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Calendar\Event';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT  `".$workingClass->_tableIdColumn()."`
				FROM    `".$workingClass->_tableName()."`
				WHERE   id = id";

			// Add Parameters
			if (!empty($parameters['code'])) {
				if ($workingClass->validCode($parameters['code'])) {
					$find_objects_query .= "
					AND     code = ?";
					$database->AddParam($parameters['code']);
				}
				else {
					$this->error("Invalid code");
					eturn [];
				}
			}

			// Order Clause
			if (isset($controls['sort']) && in_array($controls['sort'],array('code','name'))) {
				$find_objects_query .= "
					ORDER BY ".$controls['sort'];
				$find_objects_query .= " ".$controls['order'];
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
			while (list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
