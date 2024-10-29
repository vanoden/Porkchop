<?php
	namespace Package;

	class VersionList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Package\Version';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	package_versions
				WHERE	id = id
			";

			// Add Parameters
			$validationClass = new $this->_modelName();

			if (isset($parameters['package_id']) && is_numeric($parameters['package_id'])) {
				$find_objects_query .= "
				AND		package_id = ?";
				$database->AddParam($parameters['package_id']);
			}
			if (isset($parameters['major']) && is_numeric($parameters['major'])) {
				$find_objects_query .= "
				AND		major = ?";
				$database->AddParam($parameters['major']);
			}
			if (isset($parameters['minor']) && is_numeric($parameters['minor'])) {
				$find_objects_query .= "
				AND		minor = ?";
				$database->AddParam($parameters['minor']);
			}
			if (isset($parameters['build']) && is_numeric($parameters['build'])) {
				$find_objects_query .= "
				AND		build = ?";
				$database->AddParam($parameters['build']);
			}
			if (isset($parameters['status']) && $validationClass->validStatus($parameters['status'])) {
				$find_objects_query .= "
				AND		status = ?";
				$database->AddParam($parameters['status']);
			}
			if (isset($controls['sort']) && preg_match('/^(status|date_created|date_published)$/',$controls['sort'])) {
				$find_objects_query .= "
					ORDER BY ".$controls['sort'];
				if (isset($parameters['_sort_desc'])) {
					$find_objects_query .= " DESC";
				}
			}
			elseif (isset($controls['sort']) && $controls['sort'] == 'version') {
				$find_objects_query .= "
					ORDER BY major,minor,build";
				if (isset($parameters['_sort_desc'])) {
					$find_objects_query .= " DESC";
				}
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$version = new Version($id);
				array_push($objects,$version);
				$this->incrementCount();
			}
			return $objects;
		}
	}
