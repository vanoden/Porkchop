<?
	namespace Storage;

	class RepositoryFactory {
		public $error;

		public function create($type,$id = null) {
			if ($type == "Local") {
				return new Repository\Local($id);
			}
			else if ($type == "S3") {
				return new Repository\S3($id);
			}
			else {
				$this->error = "Unsupported Repository Type";
				return false;
			}
		}
		public function load($id) {
			app_log("Searching for repo ".$id);
			$repository = new Repository($id);
			app_log("Found repo ".$repository->id);
			if (! $repository->id) {
				$this->error = "Repository not found";
				return false;
			}
			if ($repository->type == "Local") {
				return new Repository\Local($repository->id);
			}
			else if ($repository->type == "S3") {
				return new Repository\S3($repository->id);
			}
			else {
				$this->error = "Unsupported Repository Type";
				return false;
			}
		}
	}
?>