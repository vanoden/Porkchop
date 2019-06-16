<?
	namespace Storage;

	class FileList {
		public $error;
		public $count;

		public function _construct() {
		}

		public function find($parameters = array()) {
			$get_objects_query = "
				SELECT	id
				FROM	storage_files
				WHERE	id = id
			";
			$bind_params = array();
			if (isset($parameters['name']) && strlen($parameters['name'])) {
				if (preg_match('/^[\w\-\_.\s]+$/',$parameters['name'])) {
					$get_objects_query .= "
						AND		name = ?";
					array_push($bind_params,$parameters['name']);
				}
				else {
					$this->error = "Invalid name";
					return false;
				}
			}

			if (isset($parameters['repository_id'])) {
				if (preg_match('/^\d+$/',$parameters['repository_id'])) {
					$get_objects_query .= "
						AND		repository_id = ?";
					array_push($bind_params,$parameters['repository_id']);
				}
				else {
					$this->error = "Invalid repository id";
					return false;
				}
			}

			if (isset($parameters['path'])) {
				$get_objects_query .= "
					AND		path = ?";
				array_push($bind_params,$parameters['path']);
			}

			query_log($get_objects_query,$bind_params);
			$rs = $GLOBALS['_database']->Execute($get_objects_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Storage::FileList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$files = array();
			while(list($id) = $rs->FetchRow()) {
				$file = new File($id);
				if ($file->readable($GLOBALS['_SESSION_']->customer->id)) {
					array_push($files,$file);
					$this->count ++;
				}
			}
			return $files;
		}
	}
?>