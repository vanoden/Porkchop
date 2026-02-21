<?php
	namespace Geography;

	class CountryList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Geography\Country';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Initialize Working Class
			$workingClass = new $this->_modelName();

			// Build Query
			$find_objects_query = "SELECT id FROM geography_countries";
			$has_where = false;
			if (isset($parameters['name']) && $parameters['name'] !== '') {
				if (preg_match('/[\*\?]/', $parameters['name']) && preg_match('/^[\*\?\w\-\.\s\-]+$/', $parameters['name'])) {
					$parameters['name'] = str_replace('*', '%', $parameters['name']);
					$parameters['name'] = str_replace('?', '_', $parameters['name']);
					$find_objects_query .= " WHERE name LIKE ?";
					$database->AddParam($parameters['name']);
					$has_where = true;
				} elseif ((method_exists($workingClass, 'validName') && $workingClass->validName($parameters['name'])) || preg_match('/^\w[\w\.\-\_\s\,]*$/', trim($parameters['name']))) {
					$find_objects_query .= " WHERE name = ?";
					$database->AddParam(trim($parameters['name']));
					$has_where = true;
				} else {
					$this->error("Invalid Name");
					return [];
				}
			}
			if (! $has_where) $find_objects_query .= " WHERE 1=1";

			$find_objects_query .= " ORDER BY view_order, name";

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Assemble Results
			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				if (!empty($parameters['default']) && $object->name == $parameters['default']) array_unshift($objects,$object);
				else array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
