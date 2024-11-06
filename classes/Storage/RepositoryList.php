<?php
	namespace Storage;

	class RepositoryList Extends \BaseListClass {
		public function _construct() {
            $this->_modelName = '\Storage\Repository';
		}

		public function findAdvanced(array $parameters, array $advanced, array $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Initialize Working Class
			$workingClass = new $this->_modelName();

			// Build Query
			$find_objects_query = "
				SELECT	id,type
				FROM	storage_repositories
				WHERE	id = id
			";

			// Bind Parameters
			if (!empty($parameters['name'])) {
				if ($workingClass->validName($parameters['name'])) {
					$find_objects_query .= "
					AND		name = ?";
					$database->AddParam($parameters['name']);
				}
				else {
					$this->setError("Invalid Name");
					return [];
				}
			}
			if (!empty($parameters['code'])) {
				if ($workingClass->validCode($parameters['code'])) {
					$find_objects_query .= "
					AND		code = ?";
					$database->AddParam($parameters['code']);
				}
				else {
					$this->setError("Invalid Code");
					return [];
				}
			}
			$find_objects_query .= "
				AND		status != 'DISABLED'";

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$objects = array();
			while(list($id,$type) = $rs->FetchRow()) {
				$factory = new RepositoryFactory();
				$object = $factory->create($type,$id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
