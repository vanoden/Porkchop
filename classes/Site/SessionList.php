<?php
	namespace Site;

	class SessionList {
		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	session_sessions
				WHERE	company_id = '".$GLOBALS['_SESSION_']->company->id."'
			";

			if (isset($parameters['code']) and preg_match('/^\w+$/',$parameters['code'])) {
				$find_objects_query .= "
				AND		code = ".$GLOBALS['_database']->qstr($parameters['code'],get_magic_quotes_gpc);
			}
			if (isset($parameters['expired'])) {
				$find_objects_query .= "
				AND		last_hit_date < sysdate() - 86400
				";
			}
			if (isset($parameters['user_id']) && preg_match('/^\d+$/',$parameters['user_id'])) {
				$find_objects_query .= "
				AND		user_id = ".$parameters['user_id'];
			}
			if (isset($parameters['date_start']) && get_mysql_date($parameters['date_start'])) {
				$threshold = get_mysql_date($parameters['date_start']);
				$find_objects_query .= "
					AND	last_hit_date >= '$threshold'
				";
			}

			if (isset($parameters['_sort']) && in_array($parameters['_sort'],array('code','last_hit_date','first_hit_date'))) {
				$find_objects_query .= "
					ORDER BY ".$parameters['_sort'];
				if (isset($parameters['_desc']) && $parameters['_desc'] == true) $find_objects_query .= " DESC";
			}

			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
					LIMIT	0,".$parameters['_limit'];
			}
            query_log($find_objects_query);
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->error = "Error finding session: ".$GLOBALS['_database']->ErrorMsg();
				print $this->error;
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new \Site\Session($id);
				array_push($objects,$object);
			}
			return $objects;
		}
	}
?>
