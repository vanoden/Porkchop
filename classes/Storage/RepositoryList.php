<?php
	namespace Storage;

	class RepositoryList Extends \BaseListClass {
		public function _construct() {
            $this->_modelName = '\Storage\Repository';
		}

		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();

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
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			$repositories = array();
			while(list($id,$type) = $rs->FetchRow()) {
				$factory = new RepositoryFactory();
				$repository = $factory->create($type,$id);
				array_push($repositories,$repository);
				$this->incrementCount();
			}
			return $repositories;
		}
	}
