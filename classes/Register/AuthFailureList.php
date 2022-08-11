<?php
	namespace Register;

	class AuthFailureList Extends \BaseListClass {
		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	register_auth_failures
				WHERE	id = id";

			$bind_params = array();

			if (isset($parameters['ip_address']) && filter_var($parameters['ip_address'], FILTER_VALIDATE_IP)) {
				$find_objects_query .= "
				AND		ip_address = ?";
				array_push($bind_params,ip2long($parameters['ip_address']));
			}
			elseif (isset($parameters['ip_address'])) {
				$this->error("Invalid ip address");
				return null;
			}
			if (isset($parameters['login']) && preg_match('/^[\w\-\.\_\@]{2,100}$/',$parameters['login'])) {
				$find_objects_query .= "
				AND		login = ?";
				array_push($bind_params,$parameters['login']);
			}
			elseif (isset($parameters['login'])) {
				$this->error("Invalid login");
				return null;
			}

			$find_objects_query .= "
				ORDER BY date_fail DESC";

			if (isset($parameters['_limit']) && is_numeric($parameters['_limit'])) {
				$find_objects_query .= "
				LIMIT ".$parameters['_limit'];
			}

			query_log($find_objects_query,$bind_params,true);
			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new \Register\AuthFailure($id);
				array_push($objects,$object);
			}
			return $objects;
		}
	}