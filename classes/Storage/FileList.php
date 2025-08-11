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
			$workingClass = new \Storage\File();

			// Build Query
			$find_objects_query = "
				SELECT sf.id
				FROM storage_files sf
				LEFT JOIN storage_file_metadata sfm ON sfm.file_id = sf.id
				WHERE sf.id = sf.id
			";
			
			// if we're looking for a specific type of file upload with a reference id
            if (!empty($parameters['type']) && !empty($parameters['ref_id'])) {
				if ($workingClass->validType($parameters['type'])) {
	                $find_objects_query .= "
					AND sfm.key = ? AND sfm.value = ?";
					$database->AddParam($parameters['type']);
					$database->AddParam($parameters['ref_id']);
					//print_r($parameters['type']);
				}
				else {
					$this->error("Invalid type");
					return [];
				}
            }
			if (!empty($parameters['mime_type']) && preg_match('/^(image|application|audio|video|text)\/?\w*\%?$/',$parameters['mime_type'])) {
				$find_objects_query .= "
					AND sf.mime_type LIKE ?";
				$database->AddParam($parameters['mime_type']);
			}

			if (!empty($parameters['name'])) {
				if ($workingClass->validName($parameters['name'])) {
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
				if (is_numeric($parameters['repository_id'])) {
					$repository = new \Storage\Repository($parameters['repository_id']);
					if ($repository->exists()) {
						$find_objects_query .= "
							AND sf.repository_id = ?";
						$database->AddParam($parameters['repository_id']);
					}
					else {
						$this->error("Repository not found-");
						return [];
					}
				}
				else {
					$this->error("Invalid repository ID");
					return [];
				}
			}

			if (!empty($parameters['path'])) {
				if ($workingClass->validPath($parameters['path'])) {
					$find_objects_query .= "
						AND sf.path = ?";
					$database->AddParam($parameters['path']);
				}
				else {
					$this->error("Invalid path");
					return [];
				}
			}

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Assemble Results
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
