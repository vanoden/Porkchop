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
			$get_members_query = "
				SELECT	user_id
				FROM	register_users_roles
				WHERE	role_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_members_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
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
		
		public function addPrivilege($new_privilege) {
			if (is_numeric($new_privilege))
				$privilege = new \Register\Privilege($new_privilege);
			else {
				$privilege = new \Register\Privilege();
				if (! $privilege->get($new_privilege)) {
					$this->error("Can't get privilege $new_privilege");
					return false;
				}
			}
			$add_privilege_query = "
				INSERT	INTO	register_roles_privileges
				VALUES  (?,?)
			";
			$GLOBALS['_database']->Execute($add_privilege_query,array($this->id,$privilege->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
			else return true;
		}

		/**
		 * Drop a privilege from a role
		 * @param mixed $privilege_id 
		 * @return bool 
		 */
		public function dropPrivilege($privilege_id) {
			$this->clearError();

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

			// Build Query
			$get_privileges_query = "
				SELECT	privilege_id
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
			while(list($id) = $rs->FetchRow()) {
				$privilege = new \Register\Privilege($id);
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
