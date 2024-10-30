<?php
	namespace Storage;

	class DirectoryList Extends \BaseListClass {
        public function __construct() {
            $this->_modelName = '\Storage\Directory';
        }

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			if (! isset($parameters['repository_id'])) {
				$this->error("Repository ID Required");
				return null;
			}

			// Build Query
			$find_objects_query = "
				SELECT	DISTINCT path
				FROM	storage_files
				WHERE	id = id";

			// Add Parameters
			if (!empty($parameters['path'])) {
				if ($workingClass->validPath($parameters['path'])) {
					$regex = "^".$parameters['path']."[^\/]+";
					$find_objects_query .= "
					AND		path != ?
					AND		path REGEXP ?";
					$database->AddParam($parameters['path']);
					$database->AddParam($regex);
				}
				else {
					$this->error("Invalid Path");
					return [];
				}
			}
			$find_objects_query .= "
				AND		repository_id = ?
			";
			$database->AddParam($parameters['repository_id']);
            app_log("Getting directories for ".$parameters['repository_id']." in ".$parameters['path'],"info");

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return [];
			}

			$directories = array();
			if (preg_match("/^\/(.+)/",$parameters['path'],$matches)) {
				$directory = new \Storage\Directory();
				$directory->get("/".$matches[1]);
				$directory->display("..");
				array_push($directories,$directory);
			}
			while(list($path) = $rs->FetchRow()) {
				$directory = new \Storage\Directory();
				app_log("Adding directory $path");
				$directory->getInPath($parameters['repository_id'],$path);
				array_push($directories,$directory);
				$this->incrementCount();
			}
			app_log("Found ".$this->count()." directories in $path");
			return $directories;
		}
	}
