<?
	namespace Storage;

	class RepositoryList {
		public $error;
		public $count;

		public function _construct() {
		}

		public function find($parameters = array()) {
			$get_objects_query = "
				SELECT	id,type
				FROM	storage_repositories
				WHERE	id = id
			";
			if (isset($parameters['code']) && strlen($parameters['code'])) {
				$get_objects_query .= "
				AND		code = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc());
			}
			$rs = $GLOBALS['_database']->Execute(
				$get_objects_query
			);
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
?>