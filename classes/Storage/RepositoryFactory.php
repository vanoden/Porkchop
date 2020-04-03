<?php
	namespace Storage;

	class RepositoryFactory {
	
		public $error;

        /**
         * create new repo
         *
         * local|file|filesystem $type
         * int $id
         */
		public function create($type, $id = null) {
			if (preg_match('/^(local|file|filesystem)$/i', $type)) {
				return new Repository\Local($id);
			} else if (preg_match('/^(s3|sss|aws)$/i', $type)) {
				return new Repository\S3($id);
			} else if (preg_match('/^(google|google_drive|google drive|drive)$/i', $type)) {
				$this->error = "Google Drive not yet supported";
				return false;
			} else if (preg_match('/^dropbox$/i', $type)) {
				$this->error = "DropBox not yet supported";
				return false;
			} else {
				$this->error = "Unsupported Repository Type";
				return false;
			}
		}

        /**
         * find by name
         *
         * string $name
         */
		public function find($name) {
			$repository = new Repository();
			$repository->find($name);
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
		
        /**
         * find by name
         *
         * int $id
         */
		public function load($id) {
			$repository = new Repository($id);
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
		
        /**
         * find by code
         *
         * string $id
         */
		public function get($code) {
			$repository = new Repository();
			$repository->get($code);
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
