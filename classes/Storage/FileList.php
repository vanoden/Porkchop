<?php
	namespace Storage;

	class FileList Extends \BaseListClass {
		
		public function _construct() {
            $this->_modelName = '\Storage\File';
		}

        /**
         * construct a new list of files
         * 
         * @param array $parameters, name value pairs to find files by
         */
		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT sf.id
				FROM storage_files sf
				LEFT JOIN storage_file_metadata sfm ON sfm.file_id = sf.id
				WHERE sf.id = sf.id
			";
			
			// if we're looking for a specific type of file upload with a reference id
            if (isset($parameters['type']) && strlen($parameters['type']) && !empty($parameters['ref_id'])) {
                $find_objects_query .= "
				AND sfm.key = ? AND sfm.value = ?";
				$database->AddParam($parameters['type']);
				$database->AddParam($parameters['ref_id']);
            }

			if (isset($parameters['name']) && strlen($parameters['name'])) {
				if (preg_match('/^[\w\-\_.\s]+$/',$parameters['name'])) {
					$find_objects_query .= "
						AND sf.name = ?";
						$database->AddParam($parameters['name']);
				}
				else {
					$this->error("Invalid name");
					return [];
				}
			}

			if (isset($parameters['repository_id'])) {
				if (preg_match('/^\d+$/',$parameters['repository_id'])) {
					$find_objects_query .= "
						AND sf.repository_id = ?";
					$database->AddParam($parameters['repository_id']);
				}
				else {
					$this->error("Invalid repository ID");
					return [];
				}
			}

			if (isset($parameters['path'])) {
				$find_objects_query .= "
					AND sf.path = ?";
				$database->AddParam($parameters['path']);
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}
			
			$files = array();
			while(list($id) = $rs->FetchRow()) {
				$file = new File($id);
				if ($file->readable($GLOBALS['_SESSION_']->customer->id)) {
					array_push($files,$file);
					$this->incrementCount();
				}
			}
			return $files;
		}
	}
