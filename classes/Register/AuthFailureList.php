<?php
	namespace Register;

	class AuthFailureList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Register\AuthFailure';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$find_objects_query = "
				SELECT	id
				FROM	register_auth_failures
				WHERE	id = id";

			// Add Parameters
			$validationClass = new $this->_modelName;
			if (isset($parameters['ip_address'])) {
				if (filter_var($parameters['ip_address'], FILTER_VALIDATE_IP)) {
					$find_objects_query .= "
					AND		ip_address = ?";
					$database->AddParam(ip2long($parameters['ip_address']));
				}
				else {
					$this->error("Invalid ip address");
					return [];
				}
			}

			// Records only after start date if provided
			if (isset($parameters['date_start']) && get_mysql_date($parameters['date_start'])) {
				$find_objects_query .= "
				AND		date_fail > ?";
				$database->AddParam($parameters['date_start']);
			}

			// Records only matchin provided login
			if (isset($parameters['login']) && preg_match('/^[\w\-\.\_\@]{2,100}$/',$parameters['login'])) {
				$find_objects_query .= "
				AND		login = ?";
				$database->AddParam($parameters['login']);
			}
			elseif (isset($parameters['login'])) {
				$this->error("Invalid login");
				return [];
			}

			// Order Clause
			$find_objects_query .= "
				ORDER BY date_fail DESC";

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}