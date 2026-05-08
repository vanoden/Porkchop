<?php
	namespace Site;

	class APICommunicationList Extends \BaseListClass {
		public function find($parameters = array()) {
			$this->clearError();
			$this->resetCount();
	
			$database = new \Database\Service();

			$get_event_query = "
				SELECT	mc.session_id
				FROM	monitor_communications mc,
						session_sessions ss,
						register_users ru
				WHERE	mc.session_id = ss.id
				AND		ss.user_id = ru.id
				AND		ss.company_id = ?
			";
			$database->addParam($GLOBALS['_SESSION_']->company->id);

			if (!empty($parameters['customer_id'])) {
				$customer = new \Register\Customer($parameters['customer_id']);
				if (!$customer->id) $this->notFound("Account not found");
				$get_event_query .= "
				AND		ru.id = ?";
				$database->AddParam($customer->id);
			}
			elseif (!empty($parameters['account'])) {
				$account = new \Register\Customer();
				if (!$account->get($parameters['account'])) {
					$this->error("Account not found");
					return array();
				}
				$get_event_query .= "
				AND		ru.id = ?";
				$database->AddParam($account->id);
			}

			if (!empty($parameters['account_type'])) {
				if ($parameters['account_type'] == 'automation') {
					$get_event_query .= "
				AND		ru.automation = 1";
				}
				elseif ($parameters['account_type'] == 'human') {
					$get_event_query .= "
				AND		(ru.automation = 0 OR ru.automation IS NULL)";
				}
			}

			if (!empty($parameters['method'])) {
				if (!preg_match('/^[a-zA-Z0-9_]+$/',$parameters['method'])) {
					$this->error("Invalid method");
					return array();
				}
				// Use JSON_EXTRACT for better performance (MySQL 5.7+ / MariaDB 10.2+)
				// The method is stored at the top level of the JSON: {"method":"addReading",...}
				// Since method is already validated, we can safely escape it
				$method_escaped = $GLOBALS['_database']->qstr($parameters['method']);
				$get_event_query .= "
				AND		JSON_UNQUOTE(JSON_EXTRACT(mc.request, '$.method')) = ".$method_escaped;
			}

			if (!empty($parameters['result'])) {
				if ($parameters['result'] == 'unknown') {
					$get_event_query .= "
				AND		mc.response not like '%\"success\":%'";
				}
				elseif ($parameters['result'] == 'success') {
					$get_event_query .= "
				AND		mc.response like '%\"success\":1%'";
				}
				elseif ($parameters['result'] == 'error') {
					$get_event_query .= "
				AND		mc.response like '%\"success\":0%'";
				}
			}

			if (isset($parameters['date_start']) && get_mysql_date($parameters['date_start'])) {
				$get_event_query .= "
				AND		ss.last_hit_date >= ?";
				$database->addParam(get_mysql_date($parameters['date_start']));
			}

			$get_event_query .= "
				ORDER BY mc.timestamp DESC, mc.session_id DESC
			";

			if (isset($parameters['_limit']) && preg_match('/^\d+$/',$parameters['_limit']))
				$get_event_query .= "
				LIMIT	0,".$parameters['_limit'];

			$rs = $database->Execute($get_event_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			$events = array();
			while (list($session_id) = $rs->FetchRow()) {
				$event = new APICommunication($session_id);
				array_push($events,$event);
				$this->incrementCount();
			}
			return $events;
		}

		/**
		 * Get the last communication from this device
		 * @param array $parameters 
		 * @return null|Communication 
		 */
		public function last($parameters = array(), $controls = array()): ?APICommunication {
			$this->clearError();
			$this->resetCount();
	
			$database = new \Database\Service();

			$get_event_query = "
				SELECT	mc.session_id
				FROM	monitor_communications mc,
						session_sessions ss
				WHERE	mc.session_id = ss.id
				AND		ss.company_id = ?
			";
			$database->AddParam($GLOBALS['_SESSION_']->company->id);

			if (!empty($parameters['customer_id'])) {
				$customer = new \Register\Customer($parameters['customer_id']);
				if (!$customer->id) $this->notFound("Account not found");
				$get_event_query .= "
				AND		ss.user_id = ?";
				$database->AddParam($customer->id);
			}
			elseif (!empty($parameters['account'])) {
				$account = new \Register\Customer();
				if (!$account->get($parameters['account'])) {
					$this->error("Account not found");
					return null;
				}
				$get_event_query .= "
				AND		ss.user_id = ?";
				$database->AddParam($account->id);
			}

			$get_event_query .= "
				ORDER BY mc.timestamp DESC, mc.session_id DESC
				LIMIT 1
			";

			$rs = $database->Execute($get_event_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			list($session_id) = $rs->FetchRow();
			return new APICommunication($session_id);
		}
	}
