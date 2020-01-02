<?php
	namespace Build;

	class RepositoryList {
		private $_error;
		private $_count;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	build_repositories
				WHERE	id = id";
			
			$bind_params = array();

			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Build::RepositoryList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$repositories = array();
			while (list($id) = $rs->FetchRow()) {
				$repository = new Repository($id);
				array_push($repositories,$repository);
				$this->_count ++;
			}
			return $repositories;
		}

		public function count() {
			return $this->_count;
		}

		public function error() {
			return $this->_error;
		}
	}
?>
