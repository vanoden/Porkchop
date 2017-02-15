<?php
	namespace Site;

	class HitList {
		public $errno;
		public $error;

		function find($parameters = array()) {
			$find_objects_query .= "
				SELECT	id
				FROM	session_hits
				WHERE	id = id
			";

			if ($parameters['session_id'])
				$find_objects_query .= "
					AND	session_id = ".$GLOBALS['_database']->qstr($parameters['session_id'],get_magic_quotes_gpc);
			$find_objects_query .= "
				ORDER BY id desc
			";
			if (preg_match('/^\d+$/',$parameters['_limit']))
				$find_objects_query .= "
					limit ".$parameters['_limit'];
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "SQL Error in SessionHitList::find: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$hits = array();
			while (list($id) = $rs->FetchRow()) {
				$hit = new SessionHit($id);
				array_push($hits,$hit);
			}
			return $hits;
		}
	}
?>