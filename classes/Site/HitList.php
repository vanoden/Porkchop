<?php
	namespace Site;

	class HitList {
		public $errno;
		public $error;

		function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	session_hits
				WHERE	id = id
			";

			$bind_params = array();

			if ($parameters['session_id']) {
				$find_objects_query .= "
					AND	session_id = ?";
				array_push($bind_params,$parameters['session_id']);
			}

			$find_objects_query .= "
				ORDER BY id desc
			";
			if (preg_match('/^\d+$/',$parameters['_limit']))
				$find_objects_query .= "
					limit ".$parameters['_limit'];
			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->error = "SQL Error in Session::HitList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$hits = array();
			while (list($id) = $rs->FetchRow()) {
				$hit = new Hit($id);
				array_push($hits,$hit);
			}
			return $hits;
		}
	}
