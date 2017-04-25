<?
	namespace Package;

	class PackageList {
		public $error;
		public $count = 0;

		public function find($parameters) {
			$find_objects_query = "
				SELECT	id
				FROM	package_packages
				WHERE	id = id
			";

			if (isset($parameters['code']) and preg_match('/^[\w\-\_\.\s]+$/',$parameters['code']))
				$find_objects_query .= "
				AND		code = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc());

			if (isset($parameters['name']) and preg_match('/^[\w\-\_\.\s]+$/',$parameters['name']))
				$find_objects_query .= "
				AND		name = ".$GLOBALS['_database']->qstr($parameters['name'],get_magic_quotes_gpc());

			if (isset($parameters['repository_id']) and preg_match('/^\d+$/',$parameters['repository_id']))
				$find_objects_query .= "
				AND		repository_id = ".$GLOBALS['_database']->qstr($parameters['repository_id'],get_magic_quotes_gpc());


			if (isset($parameters['status']) and preg_match('/^(NEW|ACTIVE|HIDDEN)$/',$parameters['status']))
				$find_objects_query .= "
				AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in Package::PackageList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$package = new Package($id);
				array_push($objects,$package);
				$this->count ++;
			}
			return $objects;
		}
	}
?>