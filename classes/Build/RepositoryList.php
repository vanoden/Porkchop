<?php
	namespace Build;

	class RepositoryList Extends \BaseListClass {
		public function __contruct() {
			$this->_modelName = 'Build\Repository';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$database = new \Database\Service();

			// Build the query
			$find_objects_query = "
				SELECT	id
				FROM	build_repositories
				WHERE	id = id";

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$repositories = array();
			while (list($id) = $rs->FetchRow()) {
				$repository = new Repository($id);
				array_push($repositories,$repository);
				$this->incrementCount();
			}
			return $repositories;
		}
	}
