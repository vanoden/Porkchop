<?php
	namespace Package;

	class VersionList {
		public $error;
		public $count = 0;

		public function find($parameters) {
			$find_objects_query = "
				SELECT	id
				FROM	package_versions
				WHERE	id = id
			";

			if (isset($parameters['package_id']) and preg_match('/^\d+$/',$parameters['package_id']))
				$find_objects_query .= "
				AND		package_id = ".$GLOBALS['_database']->qstr($parameters['package_id'],get_magic_quotes_gpc());

			if (isset($parameters['major']) and preg_match('/^\d+$/',$parameters['major']))
				$find_objects_query .= "
				AND		major = ".$GLOBALS['_database']->qstr($parameters['major'],get_magic_quotes_gpc());

			if (isset($parameters['minor']) and preg_match('/^\d+$/',$parameters['minor']))
				$find_objects_query .= "
				AND		minor = ".$GLOBALS['_database']->qstr($parameters['minor'],get_magic_quotes_gpc());

			if (isset($parameters['build']) and preg_match('/^\d+$/',$parameters['build']))
				$find_objects_query .= "
				AND		build = ".$GLOBALS['_database']->qstr($parameters['build'],get_magic_quotes_gpc());

			if (isset($parameters['status']) and preg_match('/^(NEW|PUBLISHED|HIDDEN)$/',$parameters['status']))
				$find_objects_query .= "
				AND		status = ".$GLOBALS['_database']->qstr($parameters['status'],get_magic_quotes_gpc());

			if (isset($parameters['_sort']) and preg_match('/^(status|date_created|date_published)$/',$parameters['_sort'])) {
				$find_objects_query .= "
					ORDER BY ".$parameters['_sort'];
				if (isset($parameters['_sort_desc'])) {
					$find_objects_query .= " DESC";
				}
			}
			elseif (isset($parameters['_sort']) and $parameters['_sort'] == 'version') {
				$find_objects_query .= "
					ORDER BY major,minor,build";
				if (isset($parameters['_sort_desc'])) {
					$find_objects_query .= " DESC";
				}
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in Package::PackageList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$version = new Version($id);
				array_push($objects,$version);
				$this->count ++;
			}
			return $objects;
		}
	}
