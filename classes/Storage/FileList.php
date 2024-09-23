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
		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

			$get_objects_query = "
				SELECT sf.id
				FROM storage_files sf
				LEFT JOIN storage_file_metadata sfm ON sfm.file_id = sf.id
				WHERE sf.id = sf.id
			";
			$bind_params = array();
			
			// if we're looking for a specific type of file upload with a reference id
            if (isset($parameters['type']) && strlen($parameters['type']) && !empty($parameters['ref_id'])) {
                $get_objects_query .= "
				AND sfm.key = ? AND sfm.value = ?";
				array_push($bind_params, $parameters['type']);
				array_push($bind_params, $parameters['ref_id']);
            }

			if (isset($parameters['name']) && strlen($parameters['name'])) {
				if (preg_match('/^[\w\-\_.\s]+$/',$parameters['name'])) {
					$get_objects_query .= "
						AND sf.name = ?";
					array_push($bind_params,$parameters['name']);
				} else {
					$this->error("Invalid name");
					return false;
				}
			}

			if (isset($parameters['repository_id'])) {
				if (preg_match('/^\d+$/',$parameters['repository_id'])) {
					$get_objects_query .= "
						AND sf.repository_id = ?";
					array_push($bind_params,$parameters['repository_id']);
				} else {
					$this->error("Invalid repository ID");
					return false;
				}
			}

			if (isset($parameters['path'])) {
				$get_objects_query .= "
					AND sf.path = ?";
				array_push($bind_params,$parameters['path']);
			}

			query_log($get_objects_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_objects_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
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
