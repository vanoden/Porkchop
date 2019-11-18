<?php
	namespace Build;

	class Commit {
		public $id;
		private $_error;

		public function __construct($id = null) {
			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
			if ($parameters['repository_id']) {
				$repository = new Repository($parameters['repository_id']);
				if (! $repository->id) {
					$this->_error = "Repository not found";
					return false;
				}
			}
			else {
				$this->_error = "Repository id required";
			}
			if (! isset($parameters['hash']) || ! preg_match('/^\w+$/',$parameters['hash'])) {
				$this->_error = "hash required";
				return false;
			}

			$add_object_query = "
				INSERT
				INTO	build_commits
				(		repository_id,hash,`timestamp`)
				VALUES
				(		?,?,sysdate())
			";
			$GLOBALS['_database']->Execute($add_object_query,array($repository->id,$parameters['number']));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Build::Commit::add(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
		}

		public function update($parameters = array()) {
			return $this->details();
		}

		public function get($repo_id,$hash) {
			$repository = new Repo($repo_id);
			if (! $repository->id) {
				$this->_error = "Repository not found";
				return false;
			}

			$get_object_query = "
				SELECT	id
				FROM	build_commits
				WHERE	repository_id = ?
				AND		hash = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_object_query,array($repository->id,$hash));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Commit::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id > 0) {
				app_log("Found commit ".$this->id);
				return $this->details();
			}
			return false;
		}
		public function details() {
			$get_object_query = "
				SELECT	*
				FROM	build_commits
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->_error = "SQL Error in Build::Commit::details(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$object = $rs->FetchNextObject(false);
			if ($object->id) {
				$this->id = $object->id;
				$this->repository_id = $object->repository_id;
				$this->hash = $object->hash;
				$this->timestamp = $object->timestamp;
				return true;
			}
			else {
				$this->id = null;
				return false;
			}
		}

		public function error() {
			return $this->_error;
		}
	}