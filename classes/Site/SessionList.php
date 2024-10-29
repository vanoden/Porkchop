<?php
	namespace Site;

	class SessionList Extends \BaseListClass{
		public function __construct() {
			$this->_modelName = '\Site\Session';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	`".$workingClass->_tableIdColumn()."`
				FROM	`".$workingClass->_tableName()."`
				WHERE	company_id = ?";

			$database->AddParam($GLOBALS['_SESSION_']->company->id);

			// Add Parameters
			if (!empty($parameters['code'])) {
				if ($workingClass->validCode($parameters['code'])) {
					$find_objects_query .= "
					AND		code = ?";
					$database->AddParam($parameters['code']);
				}
				else {
					$this->error("Invalid code");
					return [];
				}
			}

			if (!empty($parameters['expired']) && is_bool($parameters['expired']) && $parameters['expired']) {
				$find_objects_query .= "
				AND		last_hit_date < sysdate() - 86400
				";
			}

			if (!empty($parameters['user_id']) && is_numeric($parameters['user_id'])) {
				$user = new \Register\Person($parameters['user_id']);
				if ($user->exists()) {
					$find_objects_query .= "
					AND		user_id = ?";
					$database->AddParam($parameters['user_id']);
				}
				else {
					$this->error("User not found");
					return [];
				}
			}

			if (isset($parameters['date_start']) && get_mysql_date($parameters['date_start'])) {
				$threshold = get_mysql_date($parameters['date_start']);
				$find_objects_query .= "
					AND	last_hit_date >= ?";
				$database->AddParam($threshold);
			}

			// Order Clause
			if (isset($controls['sort']) && in_array($controls['sort'],array('code','last_hit_date','first_hit_date'))) {
				$find_objects_query .= "
					ORDER BY ".$controls['sort'];
				$find_objects_query .= " ".$controls['order'];
			}

			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Build Results
			$objects = [];
			while (list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
