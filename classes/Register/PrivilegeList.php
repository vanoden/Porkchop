<?php
	namespace Register;

	class PrivilegeList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Privilege';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName();

			// Build Query
			$find_objects_query = "
				SELECT  rp.id
				FROM    register_privileges rp
				WHERE   id = id
			";

			// Add Parameters
			if (!empty($parameters['name'])) {
				// Handle Wildcards
				if (preg_match('/[\*\?]/',$parameters['name']) && preg_match('/^[\*\?\w\-\.\s]+$/',$parameters['name'])) {
					$parameters['name'] = str_replace('*','%',$parameters['name']);
					$parameters['name'] = str_replace('?','_',$parameters['name']);
					$find_objects_query .= "
					AND	name LIKE ?";
					$database->AddParam($parameters['name']);
				}
				// Handle Exact Match
				elseif ($workingClass->validName($parameters['name'])) {
					$find_objects_query .= "
					AND		name = ?";
					$database->AddParam($parameters['name']);
				}
				else {
					$this->error("Invalid name");
					return [];
				}
			}

			if (!empty($parameters['module'])) {
				if ($workingClass->validModule($parameters['module'])) {
					$find_objects_query .= "
					AND		module = ?";
					$database->AddParam($parameters['module']);
				}
				else {
					$this->error("Invalid module");
					return [];
				}
			}

			// Order Clause
			if (isset($controls['sort'])) {
				if ($controls['sort'] == 'module') $find_objects_query .= "
				ORDER BY `module`";
			}
			else $find_objects_query .= "
				ORDER BY `name`";

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
            $rs = $database->Execute($find_objects_query);
            if (! $rs) {
                $this->SQLError($database->ErrorMsg());
                return [];
            }

            $objects = array();
            while (list($id) = $rs->FetchRow()) {
                $object = new $this->_modelName($id);
                array_push($objects,$object);
                $this->incrementCount();
            }
            return $objects;
		}
	}
