<?php
	namespace Register;

	class Privilege Extends \BaseModel {

		public string $description = "";
		public string $name = "";
		public string $module = "";

		/**
		 * Constructor
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'register_privileges';
			$this->_tableUKColumn = 'name';
			$this->_cacheKeyPrefix = 'register.privilege';
			parent::__construct($id);
		}

		/**
		 * Add a new privilege
		 * @param array $parameters 
		 * @return bool 
		 */
		public function add($parameters = []) {
			$this->clearError();

			$database = new \Database\Service();

			$add_object_query = "
				INSERT
				INTO    register_privileges
				(       name)
				VALUES
				(       ? )
			";

			$database->AddParam($parameters['name']);

			$database->Execute($add_object_query);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$this->id = $database->Insert_ID();

			// audit the add event
			$this->recordAuditEvent($this->id, 'Added new '.$this->_objectName());

			return $this->update($parameters);
		}

		/**
		 * Update a privilege
		 * @param array $parameters 
		 * @return bool 
		 */
		public function update($parameters = []): bool {
			$this->clearError();
			$this->clearCache();

			app_log("Updating privilege ".$this->id,'trace',__FILE__,__LINE__);
			$database = new \Database\Service();

			$update_object_query = "
				UPDATE      register_privileges
				SET         id = id
			";

			$audit_messages = [];

			if (!empty($parameters['name']) && $parameters['name'] != $this->name) {
				$update_object_query .= ",
				name = ?";
				$database->AddParam($parameters['name']);
				$audit_messages[] = "name changed from '" . $this->name . "' to '" . $parameters['name'] . "'";
			}

			if (!empty($parameters['module']) && $parameters['module'] != $this->module) {
				$update_object_query .= ",
				module = ?";
				$database->AddParam($parameters['module']);
				$audit_messages[] = "module changed from '" . $this->module . "' to '" . $parameters['module'] . "'";
			}

			if (!empty($parameters['description']) && $parameters['description'] != $this->description) {
				$update_object_query .= ",
				description = ?";
				$database->AddParam($parameters['description']);
				$audit_messages[] = "description changed from '" . $this->description . "' to '" . $parameters['description'] . "'";
			}

			if (count($audit_messages) == 0) {
				app_log("No changes detected, skipping update",'trace',__FILE__,__LINE__);
				return true;
			}

			$update_object_query .= "
				WHERE       id = ?
			";
			$database->AddParam($this->id);

			$database->Execute($update_object_query);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// audit the update event
			app_log("Updated privilege ".$this->id.": ".implode("; ", $audit_messages),'debug',__FILE__,__LINE__);
			$this->recordAuditEvent($this->id, implode("; ", $audit_messages));

			return $this->details();
		}

		/**
		 * Delete a privilege
		 * @return bool 
		 */
		public function delete(): bool {
			$this->clearError();
			$this->clearCache();

			$database = new \Database\Service();

			// Prepare Query
			$delete_xref_query = "
				DELETE
				FROM	register_roles_privileges
				WHERE	privilege_id = ?";

			$database->AddParam($this->id);
			$database->Execute($delete_xref_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$delete_object_query = "
				DELETE
				FROM    register_privileges
				WHERE   id = ?";
			$database->Execute($delete_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// audit the delete event
			$this->recordAuditEvent($this->id, 'Deleted '.$this->_objectName());

			return true;
		}

		/**
		 * Get Privilege Peers
		 * @return null|array 
		 */
		public function peers() {
			// Clear Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Prepare Query
			$get_object_query = "
				SELECT	rur.user_id
				FROM	register_users_roles rur,
						register_roles_privileges rrp
				WHERE	rrp.privilege_id = ?
				AND		rrp.role_id = rur.role_id
			";

			$rs = $database->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			$people = array();
			while (list($id) = $rs->FetchRow()) {
				$person = new \Register\Person($id);
				array_push($people,$person);
			}
			return $people;
		}

		/**
		 * Get Privilege Peers with specific level
		 * @param int $required_level The required privilege level
		 * @return null|array 
		 */
		public function peersWithLevel($required_level = \Register\PrivilegeLevel::CUSTOMER) {
			// Clear Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Level column exists, use it
			$get_object_query = "
				SELECT	rur.user_id, MAX(rrp.level) as max_level
				FROM	register_users_roles rur,
						register_roles_privileges rrp
				WHERE	rrp.privilege_id = ?
				AND		rrp.role_id = rur.role_id
				GROUP BY rur.user_id
				HAVING max_level >= ?
			";

			$rs = $database->Execute($get_object_query,array($this->id, $required_level));
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			$people = array();
			while (list($id, $level) = $rs->FetchRow()) {
				$person = new \Register\Person($id);
				//$person->privilege_level = $level;
				array_push($people,$person);
			}
			return $people;
		}

		/**
		 * Send a message to all people with this privilege
		 * @param mixed $message 
		 * @return null|false|void 
		 */
		public function notify($message) {
			if (! $this->id) {
				$this->error("Privilege not found");
				return null;
			}
			$members = $this->peers();
			foreach ($members as $member) {
				app_log("Sending notification to '".$member->code,'debug',__FILE__,__LINE__);
				$member->notify($message);
				if ($member->error()) {
					app_log("Error sending notification: ".$member->error(),'error',__FILE__,__LINE__);
					$this->error("Failed to send notification: ".$member->error());
					return false;
				}
			}
		}

		/**
		 * Validate Module Name
		 * @param string Name of module
		 * @return bool True if valid
		 */
		public function validModule($string) {
			$validationClass = new \Site\Module();
			return $validationClass->validName($string);
		}
	}
