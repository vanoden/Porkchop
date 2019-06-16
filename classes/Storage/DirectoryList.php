<?
	namespace Storage;

	class DirectoryList {
		private $error;
		private $count = 0;

		public function find($parameters = array()) {
			if (! isset($parameters['repository_id'])) {
				$this->error = "Repository ID Required";
				return null;
			}
			
			$bind_params = array();
			$get_dirs_query = "
				SELECT	max(path)
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
				GROUP BY path
			";
			array_push($bind_params,$parameters['repository_id']);
			query_log($get_dirs_query,$bind_params);

			$rs = $GLOBALS['_database']->Execute($get_dirs_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Storage::DirectoryList::find(): ".$GLOBALS['_database']->ErrorMsg();
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
				$directory->get($parameters['repository_id'],$path);
				array_push($directories,$directory);
				$this->count ++;
			}
			app_log("Found ".$this->count." directories in $path");
			return $directories;
		}

		public function error() {
			return $this->error;
		}

		public function count() {
			return $this->count;
		}
	}
?>
