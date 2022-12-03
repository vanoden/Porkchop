<?php
	namespace Storage;

	class RepositoryList {
		public $error;
		public $count;

		public function _construct() {}

		public function find($parameters = array()) {
			$get_objects_query = "
				SELECT	id,type
				FROM	storage_repositories
				WHERE	id = id
			";

			$bind_params = array();
			if (!empty($parameters['name'])) {
				$get_objects_query .= "
				AND		name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['code']) && strlen($parameters['code'])) {
				$get_objects_query .= "
				AND		code = ?";
				array_push($bind_params,$parameters['code']);
			}
			$get_objects_query .= "
				AND		status != 'DISABLED'";

			$rs = $GLOBALS['_database']->Execute($get_objects_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Storage::RepositoryList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$repositories = array();
			while(list($id,$type) = $rs->FetchRow()) {
				$factory = new RepositoryFactory();
				$repository = $factory->create($type,$id);
				array_push($repositories,$repository);
				$this->count ++;
			}
			return $repositories;
		}
	}
