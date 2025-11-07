<?php
	namespace Register\User;

	/** @class Register\User\Statistics
	 * @brief User Statistics
	 */
	class Statistics extends \BaseClass {
		public int $user_id = 0;
		public ?\DateTime $last_login_date = null;
		public ?\DateTime $last_hit_date = null;
		public ?\DateTime $first_login_date = null;
		public ?\DateTime $last_failed_login_date = null;
		public ?\DateTime $last_password_change_date = null;
		public ?int $session_count = 0;
		public ?int $password_change_count = 0;
		public ?int $failed_login_count = 0;

		/**
		 * Constructor
		 * @param int $id 
		 * @return void 
		 */
		public function __construct(int $id = 0) {
			if ($id > 0) {
				$this->user_id = $id;
				$this->initRecord();
				$this->get();
			}
		}

		public function get(): bool {
			// Clear Previous Errors
			$this->clearError();

			// Require ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to load statistics");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	*
				FROM	register_user_statistics
				WHERE	user_id = ?
			";

			// Bind Parameters
			$database->AddParam($this->user_id);

			// Execute Query
			$rs = $database->Execute($get_object_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			if ($row = $rs->FetchRow()) {
				$this->user_id = $row['user_id'];
				$this->last_login_date = $this->convertMySQLDateTimeToDateTime($row['last_login_date']);
				$this->last_hit_date = $this->convertMySQLDateTimeToDateTime($row['last_hit_date']);
				$this->first_login_date = $this->convertMySQLDateTimeToDateTime($row['first_login_date']);
				$this->last_failed_login_date = $this->convertMySQLDateTimeToDateTime($row['last_failed_login_date']);
				$this->last_password_change_date = $this->convertMySQLDateTimeToDateTime($row['last_password_change_date']);
				$this->session_count = (int)$row['session_count'];
				$this->password_change_count = (int)$row['password_change_count'];
				$this->failed_login_count = (int)$row['failed_login_count'];
				return true;
			}
			else {
				$this->error("No statistics found for user ID {$this->user_id}");
				return false;
			}
		}

		public function update($parameters = []): bool {
			// Clear Previous Errors
			$this->clearError();

			// Require User ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to update statistics");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Update Query
			$update_object_query = "
				UPDATE	register_user_statistics
				SET		user_id = user_id";

			if (!empty($parameters['last_login_date']) && $parameters['last_login_date'] > $this->last_login_date) {
				$update_object_query .= ", last_login_date = ?";
				$this->last_login_date = $parameters['last_login_date'];
				$database->AddParam($this->convertDateTimeToMySQLDateTime($this->last_login_date));
			}
			if (!empty($parameters['last_hit_date']) && $parameters['last_hit_date'] > $this->last_hit_date) {
				$update_object_query .= ", last_hit_date = ?";
				$this->last_hit_date = $parameters['last_hit_date'];
				$database->AddParam($this->convertDateTimeToMySQLDateTime($this->last_hit_date));
			}
			if (!empty($parameters['first_login_date']) && $parameters['first_login_date'] > $this->first_login_date) {
				$update_object_query .= ", first_login_date = ?";
				$this->first_login_date = $parameters['first_login_date'];
				$database->AddParam($this->convertDateTimeToMySQLDateTime($this->first_login_date));
			}
			if (!empty($parameters['last_failed_login_date']) && $parameters['last_failed_login_date'] > $this->last_failed_login_date) {
				$update_object_query .= ", last_failed_login_date = ?";
				$this->last_failed_login_date = $parameters['last_failed_login_date'];
				$database->AddParam($this->convertDateTimeToMySQLDateTime($this->last_failed_login_date));
			}
			if (!empty($parameters['last_password_change_date']) && $parameters['last_password_change_date'] > $this->last_password_change_date) {
				$update_object_query .= ", last_password_change_date = ?";
				$this->last_password_change_date = $parameters['last_password_change_date'];
				$database->AddParam($this->convertDateTimeToMySQLDateTime($this->last_password_change_date));
			}
			if (!empty($parameters['session_count'])) {
				$update_object_query .= ", session_count = ?";
				$this->session_count = (int)$parameters['session_count'];
				$database->AddParam($this->session_count);
			}
			if (!empty($parameters['password_change_count'])) {
				$update_object_query .= ", password_change_count = ?";
				$this->password_change_count = (int)$parameters['password_change_count'];
				$database->AddParam($this->password_change_count);
			}
			if (!empty($parameters['failed_login_count'])) {
				$update_object_query .= ", failed_login_count = ?";
				$this->failed_login_count = (int)$parameters['failed_login_count'];
				$database->AddParam($this->failed_login_count);
			}
			$update_object_query .= "
				WHERE	user_id = ?
			";
			$database->AddParam($this->user_id);

			// Execute Update
			$result = $database->Execute($update_object_query);
			if (! $result) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			return true;
		}

		public function initRecord(): bool {
			// Clear Previous Errors
			$this->clearError();

			// Require User ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to initialize statistics");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Insert Query
			$insert_object_query = "
				INSERT INTO	register_user_statistics
				(	user_id,
					last_login_date
				) VALUES (
					?, sysdate()
				)
				ON DUPLICATE KEY UPDATE last_login_date = sysdate()
			";

			// Bind Parameters
			$database->AddParam($this->user_id);

			// Execute Insert
			$result = $database->Execute($insert_object_query);
			if (! $result) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			return true;
		}

		public function recordLogin(): bool {
			// Clear Previous Errors
			$this->clearError();

			// Require User ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to record login");
				return false;
			}

			// Update Statistics
			$now = new \DateTime();
			$params = [
				'last_login_date' => $now,
				'last_hit_date' => $now,
			];
			if (empty($this->first_login_date)) {
				$params['first_login_date'] = $now;
			}
			if (is_numeric($this->session_count)) {
				$params['session_count'] = $this->session_count + 1;
			} else {
				$params['session_count'] = 1;
			}

			return $this->update($params);
		}

		/** @method recordPasswordChange()
		 * Record password change
		 * @return bool
		 */
		public function recordPasswordChange(): bool {
			// Clear Previous Errors
			$this->clearError();

			// Require User ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to record password change");
				return false;
			}

			// Update Statistics
			$now = new \DateTime();
			$params = [
				'last_password_change_date' => $now,
			];
			if (is_numeric($this->password_change_count)) {
				$params['password_change_count'] = $this->password_change_count + 1;
			} else {
				$params['password_change_count'] = 1;
			}
			$params['failed_login_count'] = 0;

			return $this->update($params);
		}

		public function recordHit(): bool {
			// Clear Previous Errors
			$this->clearError();

			// Require User ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to record hit");
				return false;
			}

			// Update Statistics
			$now = new \DateTime();
			$params = [
				'last_hit_date' => $now,
			];

			return $this->update($params);
		}

		/** @method recordFailedLogin()
		 * Record failed login attempt
		 * @return bool
		 */
		public function recordFailedLogin(): bool {
			// Clear Previous Errors
			$this->clearError();

			// Require User ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to record failed login");
				return false;
			}

			// Update Statistics
			$params = [
				'failed_login_count' => is_numeric($this->failed_login_count) ? $this->failed_login_count + 1 : 1,
				'last_failed_login_date' => new \DateTime(),
			];

			return $this->update($params);
		}

		/** @method getFailedLogins()
		 * Get failed login count
		 * @return int
		 */
		public function getFailedLogins(): int {
			// Clear Previous Errors
			$this->clearError();

			// Require User ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to get failed logins");
				return 0;
			}

			return is_numeric($this->failed_login_count) ? $this->failed_login_count : 0;
		}

		/** @method resetFailedLogins()
		 * Reset failed login count
		 * @return bool
		 */
		public function resetFailedLogins(): bool {
			// Clear Previous Errors
			$this->clearError();

			// Require User ID
			if (!is_numeric($this->user_id) || $this->user_id <= 0) {
				$this->error("User ID required to reset failed logins");
				return false;
			}

			// Update Statistics
			$params = [
				'failed_login_count' => 0,
			];

			return $this->update($params);
		}

		/** @method public toArray()
		 * Convert object to array
		 * @return array
		 */
		public function toArray(): array {
			return [
				'user_id' => $this->user_id,
				'last_login_date' => $this->last_login_date ? $this->last_login_date->format('c') : null,
				'last_hit_date' => $this->last_hit_date ? $this->last_hit_date->format('c') : null,
				'first_login_date' => $this->first_login_date ? $this->first_login_date->format('c') : null,
				'last_failed_login_date' => $this->last_failed_login_date ? $this->last_failed_login_date->format('c') : null,
				'last_password_change_date' => $this->last_password_change_date ? $this->last_password_change_date->format('c') : null,
				'session_count' => $this->session_count,
				'password_change_count' => $this->password_change_count,
				'failed_login_count' => $this->failed_login_count,
			];
		}

		public function convertMySQLDateTimeToDateTime(?string $mysqlDateTime): ?\DateTime {
			if (empty($mysqlDateTime) || $mysqlDateTime === '0000-00-00 00:00:00') {
				return null;
			}
			return new \DateTime($mysqlDateTime);
		}

		public function convertDateTimeToMySQLDateTime(?\DateTime $dateTime): ?string {
			if (empty($dateTime)) {
				return null;
			}
			return $dateTime->format('Y-m-d H:i:s');
		}
	}