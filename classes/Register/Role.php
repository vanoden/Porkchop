<?php
	namespace Register;
	class Role Extends \BaseModel {

		public string $name = "";			// Name of the role
		public string $description = "";	// Description of the role
		public int $time_based_password = 0;    // Whether this role requires 2FA

		public function __construct(?int $id = 0) {
			$this->_tableName = "register_roles";
			$this->_tableUKColumn = 'name';
			$this->_cacheKeyPrefix = 'register.role';
			$this->_addFields('name', 'description', 'time_based_password');
			$this->_auditEvents = true;
			parent::__construct($id);
		}

		/**
		 * Add a new role
		 * @param array $parameters 
		 * @return bool True if add successful
		 */
		public function add($parameters = []): bool {
			$this->clearError();

			// Validate Input
			if (!$this->validName($parameters['name'])) {
				$this->error("Failed to add role, invalid name");
				return false;
			}
			$current_role = new Role();
			$current_role->get($parameters['name']);
			if ($current_role->id) {
				$this->error("Role already exists");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$add_object_query = "
				INSERT
				INTO    register_roles
				(       name)
				VALUES
				(       ?)
				ON DUPLICATE KEY UPDATE
						name = name
			";

			// Add Parameters
			$database->AddParam($parameters['name']);

			// Execute Query
			$database->execute($add_object_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			$id = $database->Insert_ID();
			if (! $id) {
				$this->error("Failed to get new role id");
				return false;
			}
			$this->id = $id;

			// audit the add event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Added new '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'add'
			));

			return $this->update($parameters);
		}

		/**
		 * Update a role
		 * @param array $parameters 
		 * @return bool True if updated successful
		 */
		public function update($parameters = []): bool {
			$this->clearError();

			// Validate Input
			if (isset($parameters['description']) && !$this->safeString($parameters['description'])) {
				$this->error("Failed to update role, invalid description");
				return false;
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Get Current Settings for Comparison
			$current_details = new Role($this->id);

			$update_description = "";

			// Build Query
			$update_object_query = "
				UPDATE	register_roles
				SET		id = id";

			// Add Parameters
			if (isset($parameters['description'])) {
				if ($parameters['description'] != $current_details->description) {
					$update_object_query .= ",
						description = ?";
					$database->AddParam($parameters['description']);
					$update_description = 'Changed '.$this->name.' description to "'.$parameters['description'].'"';
				}
			}
			
			if (isset($parameters['time_based_password'])) {
				if ($parameters['time_based_password'] != $current_details->time_based_password) {
					$update_object_query .= ",
							time_based_password = ?";
					$database->AddParam($parameters['time_based_password'] ? 1 : 0);

					$update_description = 'Changed '.$this->name.' time_based_password to "'.($parameters['time_based_password'] ? 'true' : 'false').'"';
				}
			}

			if (empty($update_description)) {
				$this->warn("No changes made to role");
				return true;
			}

			$update_object_query .= "
				WHERE	id = ?";
			$database->AddParam($this->id);

			// Execute Query
			$database->Execute($update_object_query);

			// Check for Errors
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			$this->clearCache();

			// Prepare For Audit Log Records
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => $update_description,
				'class_name' => get_class($this),
				'class_method' => 'update'
			));	

			return $this->details();
		}
		
		public function removeMembers() {
			$members = $this->members();
			foreach ($members as $member) {
				if (!$member->drop_role($this->id)) {
					$this->error($member->error());
					return false;
				}
			}
			return true;
		}

		public function members() {
			// Clear Previous Errors
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Ensure Role Exists
			if (! $this->id) {
				$this->error("Role not found");
				return null;
			}

			// Prepare Query to Get Members
			$get_members_query = "
				SELECT	user_id
				FROM	register_users_roles
				WHERE	role_id = ?
			";
			$database->AddParam($this->id);
			$rs = $database->Execute($get_members_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}
			$admins = array();
			while (list($admin_id) = $rs->FetchRow()) {
				$admin = new \Register\Admin($admin_id);
				array_push($admins,$admin);
			}
			return $admins;
		}
		
		public function hasMember($person_id) {
			$get_member_query = "
				SELECT	1
				FROM	register_users_roles
				WHERE	role_id = ?
				AND		user_id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_member_query,
				array(
					$this->id,
					$person_id
				)
			);
			if (! $GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			list($found) = $rs->FetchRow();
			if ($found == 1) return true;
			else return false;
		}
		
		public function addMember($person_id) {
			if ($this->hasMember($person_id)) {
				$this->error("Person already has role");
				return true;
			}

			$add_member_query = "
				INSERT
				INTO	register_users_roles
				(		role_id,user_id)
				VALUES
				(		?,?)
			";
			$GLOBALS['_database']->Execute(
				$add_member_query,
				array(
					$this->id,
					$person_id
				)
			);
			if (! $GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			return true;
		}

		public function notify($message) {
			if (! $this->id) {
				$this->error("Role not found");
				return null;
			}
			$members = $this->members();
			foreach ($members as $member) {
				$member = new \Register\Person($member->id);
				app_log("Sending notification to '".$member->code."' about contact form",'debug',__FILE__,__LINE__);
				$member->notify($message);
				if ($member->error()) {
					app_log("Error sending notification: ".$member->error(),'error',__FILE__,__LINE__);
					$this->error("Failed to send notification: ".$member->error());
					return false;
				}
			}
		}
		
		public function addPrivilege($new_privilege, $level = \Register\PrivilegeLevel::CUSTOMER) {
			if (is_numeric($new_privilege))
				$privilege = new \Register\Privilege($new_privilege);
			else {
				$privilege = new \Register\Privilege();
				if (! $privilege->get($new_privilege)) {
					$this->error("Can't get privilege $new_privilege");
					return false;
				}
			}
			
			// Validate privilege level
			if (!\Register\PrivilegeLevel::isValidLevel($level)) {
				$this->error("Invalid privilege level: $level");
				return false;
			}
			
			// Check if privilege already exists to determine if this is an update
			$existing_level = $this->getPrivilegeLevel($privilege->id);
			$is_update = $existing_level !== null;

		// Prevent self-privilege escalation: Users cannot increase privilege levels on roles they have
		// Exception: Users with administrator level for 'manage privileges' can modify any role
		if (!$GLOBALS['_SESSION_']->elevated() && isset($GLOBALS['_SESSION_']->customer) && $GLOBALS['_SESSION_']->customer->id) {
			$current_user = $GLOBALS['_SESSION_']->customer;
			
			// Skip escalation check if user has administrator level for 'manage privileges'
			$has_admin_privilege_manage = $current_user->has_privilege_level('manage privileges', \Register\PrivilegeLevel::ADMINISTRATOR);
			
			if (!$has_admin_privilege_manage) {
				// Check if the current user has this role
				if ($current_user->has_role_id($this->id)) {
					// User has this role - prevent them from increasing privilege levels
					// Get user's current level for this privilege
					$user_current_level = $this->getUserPrivilegeLevel($current_user->id, $privilege->id);
					
					// If the new level is higher than what the user currently has, prevent it
					if ($level > $user_current_level) {
						$this->error("You cannot increase privilege levels on roles you have");
						return false;
					}
				}
			}
		}
			
			$add_privilege_query = "
				INSERT	INTO	register_roles_privileges
				(role_id, privilege_id, level)
				VALUES  (?,?,?)
				ON DUPLICATE KEY UPDATE level = VALUES(level)
			";
			$GLOBALS['_database']->Execute($add_privilege_query,array($this->id,$privilege->id,$level));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			
			// Audit the privilege change
			$auditLog = new \Site\AuditLog\Event();
			$level_name = \Register\PrivilegeLevel::privilegeName($level);
			$old_level_name = $existing_level ? \Register\PrivilegeLevel::privilegeName($existing_level) : 'none';
			
			if ($is_update) {
				$auditLog->add(array(
					'instance_id' => $this->id,
					'description' => "Updated privilege '{$privilege->name}' level from {$old_level_name} to {$level_name} for role '{$this->name}'",
					'class_name' => get_class($this),
					'class_method' => 'addPrivilege'
				));
			} else {
				$auditLog->add(array(
					'instance_id' => $this->id,
					'description' => "Added privilege '{$privilege->name}' with level {$level_name} to role '{$this->name}'",
					'class_name' => get_class($this),
					'class_method' => 'addPrivilege'
				));
			}
			
			return true;
		}

		/**
		 * Get the maximum privilege level a user has for a specific privilege
		 * @param int $user_id The user ID
		 * @param int $privilege_id The privilege ID
		 * @return int The maximum privilege level (0 if user has no privilege)
		 */
		private function getUserPrivilegeLevel(int $user_id, int $privilege_id): int {
			$database = new \Database\Service();

			$get_level_query = "
				SELECT	MAX(rrp.level) as max_level
				FROM	register_users_roles rur,
						register_roles_privileges rrp
				WHERE	rur.user_id = ?
				AND		rrp.role_id = rur.role_id
				AND		rrp.privilege_id = ?
			";

			$database->AddParam($user_id);
			$database->AddParam($privilege_id);

			$rs = $database->Execute($get_level_query);
			if (!$rs || $database->ErrorMsg()) {
				return 0;
			}

			$row = $rs->FetchRow();
			if ($row) {
				list($max_level) = $row;
				return $max_level ?? 0;
			}

			return 0;
		}

		/**
		 * Drop a privilege from a role
		 * @param mixed $privilege_id 
		 * @return bool 
		 */
		public function dropPrivilege($privilege_id) {
			$this->clearError();

			// Get privilege information before deletion for audit
			$privilege = new \Register\Privilege($privilege_id);
			$existing_level = $this->getPrivilegeLevel($privilege_id);

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$drop_privilege_query = "
				DELETE
				FROM    register_roles_privileges
				WHERE   role_id = ?
				AND     privilege_id = ?
			";

			// Add Parameters
			$database->AddParam($this->id);
			$database->AddParam($privilege_id);

			// Execute Query
			$database->Execute($drop_privilege_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Audit the privilege removal
			$auditLog = new \Site\AuditLog\Event();
			$level_name = $existing_level ? \Register\PrivilegeLevel::privilegeName($existing_level) : 'unknown';
			
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => "Removed privilege '{$privilege->name}' with level {$level_name} from role '{$this->name}'",
				'class_name' => get_class($this),
				'class_method' => 'dropPrivilege'
			));

			return true;
		}

		/**
		 * Get the privileges for a role
		 * @return array 
		 */
		public function privileges(): array {
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Use the level column
			$get_privileges_query = "
				SELECT	privilege_id, level
				FROM	register_roles_privileges
				WHERE	role_id = ?
			";

			// Add Parameters
			$database->AddParam($this->id);

			// Execute Query
			$rs = $database->Execute($get_privileges_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Assemble Results
			$privileges = array();
			while(list($id, $level) = $rs->FetchRow()) {
				$privilege = new \Register\Privilege($id);
				$privilege->level = $level;
				array_push($privileges,$privilege);
			}
			return $privileges;
		}

		/**
		 * Check if a role has a privilege
		 * @param $param
		 * @return bool
		 */
		public function has_privilege($param): bool {
			$this->clearError();

			// Validate Input
			if (is_numeric($param)) {
				$privilege = new \Register\Privilege($param);
			}
			else {
	   			$privilege = new \Register\Privilege();
				if (! $privilege->get($param)) {
					return false;
				}
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Build Query
			$get_privilege_query = "
				SELECT	1
				FROM	register_roles_privileges
				WHERE	role_id = ?
				AND		privilege_id = ?
			";

			// Add Parameters
			$database->AddParam($this->id);
			$database->AddParam($privilege->id);

			// Execute Query
			$rs = $database->Execute($get_privilege_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Assemble Results
			list($found) = $rs->FetchRow();
			if ($found == 1) {
				return true;
			}
			else {
				return false;
			}
		}

		/**
		 * Check if a role has a privilege with specific level
		 * @param mixed $param Privilege name or ID
		 * @param int $required_level Required privilege level
		 * @return bool
		 */
		public function has_privilege_level($param, int $required_level = \Register\PrivilegeLevel::CUSTOMER): bool {
			$this->clearError();

			// Validate Input
			if (is_numeric($param)) {
				$privilege = new \Register\Privilege($param);
			}
			else {
	   			$privilege = new \Register\Privilege();
				if (! $privilege->get($param)) {
					return false;
				}
			}

			// Initialize Database Service
			$database = new \Database\Service();

			// Level column exists, use it
			$get_privilege_query = "
				SELECT	level
				FROM	register_roles_privileges
				WHERE	role_id = ?
				AND		privilege_id = ?
			";

			// Add Parameters
			$database->AddParam($this->id);
			$database->AddParam($privilege->id);

			// Execute Query
			$rs = $database->Execute($get_privilege_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			// Check if privilege exists and has sufficient level
			$row = $rs->FetchRow();
			if ($row) {
				list($level) = $row;
				return \Register\PrivilegeLevel::hasLevel($level, $required_level);
			}
			else {
				return false;
			}
		}

		/**
		 * Get privilege level for a specific privilege
		 * @param int $privilege_id The privilege ID
		 * @return int|null The privilege level or null if not found
		 */
		public function getPrivilegeLevel(int $privilege_id): ?int {
			$this->clearError();

			// Initialize Database Service
			$database = new \Database\Service();

			// Use the level column
			$get_privilege_query = "
				SELECT	level
				FROM	register_roles_privileges
				WHERE	role_id = ?
				AND		privilege_id = ?
			";

			// Add Parameters
			$database->AddParam($this->id);
			$database->AddParam($privilege_id);

			// Execute Query
			$rs = $database->Execute($get_privilege_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return null;
			}

			// Get privilege level
			$row = $rs->FetchRow();
			if ($row) {
				list($level) = $row;
				return (int)$level;
			}
			else {
				return null;
			}
		}

		/**
		 * check if a user is in a role by name
		 *
		 * @param $user_id
		 * @param $role_id
		 */
		public function checkIfUserInRole($user_id, $role_id) {
			$checkIfUserInRole = "
			SELECT * FROM `register_roles` rr
				INNER JOIN register_users_roles rur ON rr.id = rur.role_id
				WHERE rur.user_id = ? AND rr.id = ?;

			";
			$rs = $GLOBALS['_database']->Execute($checkIfUserInRole,array($user_id, $role_id));
			list($id) = $rs->FetchRow();
			if (!empty($id)) {
				return true;
			} else {
				return false;
			}
		}

		/**
		 * get roles that a group of users are in by user_id
		 *
		 * @param array $userIds, array of user ids to check
		 */
		public function getRolesforUsers($userIds = array()) {
			$getRolesforUsersQuery = "
				SELECT DISTINCT(name) FROM `register_users_roles` rur
				INNER JOIN `register_roles` rr on rur.role_id = rr.id
				WHERE user_id IN (?);
			";
			$rs = $GLOBALS['_database']->Execute($getRolesforUsersQuery,array(implode(",", $userIds)));
			$rolesList = array();        
			while(list($name) = $rs->FetchRow()) array_push($rolesList,$name);     
			return $rolesList;
		}

		public function validName($string): bool {
			if (preg_match('/^\w[\w\-\_\s]*$/',$string)) return true;
			else return false;
		}
	}
