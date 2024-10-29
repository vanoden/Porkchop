<?php
	namespace Package;

	class PackageList Extends \BaseListClass {
		public function __construct($parameters = array()) {
			$this->_modelName = "\Package\Package";
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	package_packages
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();
	
			if (isset($parameters['code']) && $validationClass->validCode($parameters['code'])) {
				$find_objects_query .= "
				AND		code = ?";
				$database->AddParam($parameters['code']);
			}
			if (isset($parameters['name']) and $validationClass->validName($parameters['name'])) {
				$find_objects_query .= "
				AND		name = ?";
				$database->AddParam($parameters['name']);
			}
			if (isset($parameters['repository_id']) && is_numeric($parameters['repository_id'])) {
				$find_objects_query .= "
				AND		repository_id = ?";
				$database->AddParam($parameters['repository_id']);
			}
			if (isset($parameters['status']) and preg_match('/^(NEW|ACTIVE|HIDDEN)$/',$parameters['status'])) {
				$find_objects_query .= "
				AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());
			}

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$package = new Package($id);
				array_push($objects,$package);
				$this->incrementCount();
			}
			return $objects;
		}
	}
