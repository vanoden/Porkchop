<?php
	namespace Register;

	class RoleList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\Role';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_objects_query = "
				SELECT	id
				FROM	register_roles
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (!empty($parameters['name'])) {
				if ($validationClass->validName($parameters['name'])) {
					$get_objects_query .= "
						AND		name = ?
					";
					$database->AddParam($parameters['name']);
				}
				else {
					$this->setError('Invalid name');
					return [];
				}
			}

			$rs = $database->Execute($get_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$roles = array();
			while (list($id) = $rs->FetchRow()) {
				$role = new Role($id);
				$this->incrementCount();
				array_push($roles,$role);
			}
			return $roles;
		}
	}
