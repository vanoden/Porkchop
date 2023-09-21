<?php
	namespace Storage;

	class DirectoryList Extends \BaseListClass {
        public function __construct ($id = 0) {
            $this->_modelName = '\Storage\Directory';
        }

		public function find($parameters = [], $controls = []) {
			if (! isset($parameters['repository_id'])) {
				$this->error("Repository ID Required");
				return null;
			}

            $bind_params = array();
			$get_dirs_query = "
				SELECT	DISTINCT path
				FROM	storage_files
				WHERE	id = id";

			if ($parameters['path']) {
				$regex = "^".$parameters['path']."[^\/]+";
				$get_dirs_query .= "
				AND		path != ?
				AND		path REGEXP ?";
				array_push($bind_params,$parameters['path'],$regex);
			}
			
			$get_dirs_query .= "
				AND		repository_id = ?
			";
			array_push($bind_params,$parameters['repository_id']);
			query_log($get_dirs_query,$bind_params,true);
            app_log("Getting directories for ".$parameters['repository_id']." in ".$parameters['path'],"info");

			$rs = $GLOBALS['_database']->Execute($get_dirs_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
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
