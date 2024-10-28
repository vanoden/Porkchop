<?php
	namespace Site;

	class SessionList Extends \BaseListClass{
		public function find($parameters = [], $controls = []) {
			$this->clearError();
			$this->resetCount();

			if (array_key_exists('_limit',$parameters)) $controls['limit'] = $parameters['_limit'];
			if (array_key_exists('_sort',$parameters)) $controls['sort'] = $parameters['_sort'];
			if (array_key_exists('_offset',$parameters)) $controls['offset'] = $parameters['_offset'];
			if (array_key_exists('_desc',$parameters) && is_bool($parameters['_desc']) && $parameters['_desc']) $controls['order'] = 'DESC';
			else $controls['order'] = 'ASC';
			if (empty($controls['offset'])) $controls['offset'] = 0;
			if (!empty($controls["order"]) && strtolower($controls['order']) != "asc") $controls['order'] = 'DESC';
			else $controls['order'] = 'ASC';

			$database = new \Database\Service();

			$find_objects_query = "
				SELECT	id
				FROM	session_sessions
				WHERE	company_id = ?";

			$database->AddParam($GLOBALS['_SESSION_']->company->id);

			if (isset($parameters['code']) && preg_match('/^\w+$/',$parameters['code'])) {
				$find_objects_query .= "
				AND		code = ?";
				$database->AddParam($parameters['code']);
			}

			if (!empty($parameters['expired'])) {
				$find_objects_query .= "
				AND		last_hit_date < sysdate() - 86400
				";
			}

			if (isset($parameters['user_id']) && preg_match('/^\d+$/',$parameters['user_id'])) {
				$find_objects_query .= "
				AND		user_id = ?";
				$database->AddParam($parameters['user_id']);
			}
			if (isset($parameters['date_start']) && get_mysql_date($parameters['date_start'])) {
				$threshold = get_mysql_date($parameters['date_start']);
				$find_objects_query .= "
					AND	last_hit_date >= ?";
				$database->AddParam($threshold);
			}

			if (isset($controls['sort']) && in_array($controls['sort'],array('code','last_hit_date','first_hit_date'))) {
				$find_objects_query .= "
					ORDER BY ".$controls['sort'];
				$find_objects_query .= " ".$controls['order'];
			}

			if (isset($controls['limit']) && is_numeric($controls['limit'])) {
				$find_objects_query .= "
					LIMIT	".$controls['offset'].",".$controls['limit'];
			}

			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new \Site\Session($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
