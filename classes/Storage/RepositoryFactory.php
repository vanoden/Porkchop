<?php
	namespace Storage;

	class RepositoryFactory Extends \BaseClass {

        /**
         * create new repo
         *
         * @param type local|file|filesystem $type
         * @param int $id - Optional
		 * @return Repository|NULL
         */
		public function create($type, $id = null): ?Repository {
		
			if (preg_match('/^(local|file|filesystem)$/i', $type)) {
				return new Repository\Local($id);
			}
            else if (preg_match('/^(s3|sss|aws)$/i', $type)) {
				return new Repository\S3($id);
			}
            else if (preg_match('/^(google|google_drive|google drive|drive)$/i', $type)) {
				$this->error("Google Drive not yet supported");
				return null;
			}
            else if (preg_match('/^dropbox$/i', $type)) {
				$this->error("DropBox not yet supported");
				return null;
			}
			else if (preg_match('/^virtual$/i', $type)) {
				return new Repository\Validation($id);
				// Virtual repository for validation purposes, not meant to be used as a real repository
			}
            else {
				$this->error("Unsupported Repository Type '$type'");
				return null;
			}
		}

		/** @method createWithID($id)
		 * Find the repository with the given ID and return an instance of the appropriate class
		 */
		public function createWithID($id) {
			$repository = new Repository\Validation($id);
			if (! $repository->id) {
				$this->error("Repository not found");
				return null;
			}
			return $this->create($repository->type, $id);
		}

		/** @method createWithCode($code)
		 * Find the repository with the given code and return an instance of the appropriate class
		 */
		public function createWithCode($code) {
			$repository = new Repository\Validation();
			$repository->get($code);
			if (! $repository->id) {
				$this->error("Repository not found");
				return null;
			}
			return $this->create($repository->type, $repository->id);
		}

		/** @method createWithName($name)
		 * Find the repository with the given name and return an instance of the appropriate class
		 */
		public function createWithName($name) {
			$repositoryList = new RepositoryList();
			$repositories = $repositoryList->find(['name' => $name]);
			if (count($repositories) == 0) {
				$this->error("Repository not found");
				return null;
			}
			else if (count($repositories) > 1) {
				$this->error("Multiple repositories found with that name");
				return null;
			}
			$repository = $repositories[0];
			return $this->create($repository->type, $repository->id);
		}

		/** @method find($name)
		 * Find the repository with the given name and return an instance of the appropriate class
		 */
		public function find($name) {
			return $this->createWithName($name);
		}

		/**
		 * Get a List of Repository Types
		 * @return array
		 */
		public function types() {
			return array(
				'local'	=> 'Local',
				's3'	=> 'Amazon S3',
				'google' => 'Google Drive',
				'dropbox' => 'DropBox'
			);
		}
	}
