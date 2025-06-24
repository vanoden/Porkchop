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

	/**
	 * Purge expired sessions from the database
	 * @param array $parameters - Optional parameters for filtering
	 *   - last_hit_date: delete only records with a last_hit_date before this date (defaults to 6 months ago, never less than 48 hours)
	 *   - max_records: delete no more than this number of records
	 *   - user_login: delete only records for the specified account
	 * @return array - Array with 'success' (bool) and 'count' (int) keys
	 */
	public function purgeExpired($parameters = []): array {
		$this->clearError();
		
		// Set default cutoff date (6 months ago, but never less than 48 hours)
		$defaultCutoff = date('Y-m-d H:i:s', strtotime('-6 months'));
		$minimumCutoff = date('Y-m-d H:i:s', strtotime('-48 hours'));
		
		$cutoffDate = $parameters['last_hit_date'] ?? $defaultCutoff;
		
		// Ensure cutoff date is not less than 48 hours ago
		if (strtotime($cutoffDate) > strtotime($minimumCutoff)) {
			$cutoffDate = $minimumCutoff;
		}
		
		// Initialize Database Service
		$database = new \Database\Service();
		
		// Build query to find sessions to purge
		$find_sessions_query = "
			SELECT	s.id, s.user_id, s.last_hit_date
			FROM	session_sessions s
			WHERE	s.last_hit_date < ?
		";
		$database->AddParam($cutoffDate);
		
		// Add user filter if specified
		if (!empty($parameters['user_login'])) {
			$customer = new \Register\Customer();
			if ($customer->get($parameters['user_login'])) {
				$find_sessions_query .= " AND s.user_id = ?";
				$database->AddParam($customer->id);
			} else {
				return ['success' => false, 'count' => 0, 'error' => 'User not found'];
			}
		}
		
		// Order by last_hit_date (oldest first)
		$find_sessions_query .= " ORDER BY s.last_hit_date ASC";
		
		// Add limit if specified
		if (!empty($parameters['max_records']) && is_numeric($parameters['max_records'])) {
			$find_sessions_query .= " LIMIT " . intval($parameters['max_records']);
		}
		
		// Execute query
		$rs = $database->Execute($find_sessions_query);
		if (!$rs) {
			$this->SQLError($database->ErrorMsg());
			return ['success' => false, 'count' => 0, 'error' => $this->error()];
		}
		
		$deletedCount = 0;
		
		// Process each session
		while ($sessionData = $rs->FetchRow()) {
			$sessionId = $sessionData[0];
			$userId = $sessionData[1];
			$sessionLastHit = $sessionData[2];
			
			// Update customer's last_hit_date if this session is newer
			if ($userId > 0) {
				$customer = new \Register\Customer($userId);
				if ($customer->id) {
					$customerLastHit = $customer->getMetadata('last_hit_date');
					if (empty($customerLastHit) || strtotime($sessionLastHit) > strtotime($customerLastHit)) {
						$customer->setMetadata('last_hit_date', $sessionLastHit);
					}
				}
			}
			
			// Delete the session
			$session = new \Site\Session($sessionId);
			if ($session->expire()) {
				$deletedCount++;
				app_log("Purged expired session ID: $sessionId (last hit: $sessionLastHit)", 'info', __FILE__, __LINE__);
			} else {
				app_log("Failed to purge session ID: $sessionId - " . $session->error(), 'error', __FILE__, __LINE__);
			}
		}
		
		return ['success' => true, 'count' => $deletedCount];
	}
}
