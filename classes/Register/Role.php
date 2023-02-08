<?php
	namespace Register;
	class Role Extends \BaseClass {
		public $id;
		public $name;
		public $description;

		public function __construct(int $id = null) {
			$this->_tableName = "register_roles";
			$this->_tableUKColumn = 'name';

			if (isset($id) && is_numeric($id)) {
				$this->id = $id;
				$this->details();
			}
		}

        public function add($parameters = array()) {
            if (!$this->validName($parameters['name'])) {
                $this->error("Failed to add role, invalid name");
                return null;
            }
			$current_role = new Role();
			$current_role->get($parameters['name']);
			if ($current_role->id) {
				$this->error("Role already exists");
				return false;
			}

            $add_object_query = "
                INSERT
                INTO    register_roles
                (       name)
                VALUES
                (       ?)
				ON DUPLICATE KEY UPDATE
						name = name
            ";
            $GLOBALS['_database']->execute(
				$add_object_query,
				array(
					$parameters['name']
				)
			);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->update($parameters);
        }

		public function update($parameters = array()) {
			$update_object_query = "
				UPDATE	register_roles
				SET		id = id";

			$bind_params = array();
			if (isset($parameters['description'])) {
				$update_object_query .= ",
						description = ?";
				array_push($bind_params,$parameters['description']);
			}

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
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
		
		public function details() {
			$get_object_query = "
				SELECT	id,
						name,
						description
				FROM	register_roles
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			if ($object = $rs->FetchRow()){
				$this->name = $object['name'];
				$this->description = $object['description'];
				return true;
			}
			else {
				return false;
			}
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

        public function dropPrivilege($privilege_id) {
            $drop_privilege_query = "
                DELETE
                FROM    register_roles_privileges
                WHERE   role_id = ?
                AND     privilege_id = ?
            ";
            $GLOBALS['_database']->Execute($drop_privilege_query,array($this->id,$privilege_id));
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return false;
            }
            return true;
        }

		public function privileges() {
			$get_privileges_query = "
				SELECT	privilege_id
				FROM	register_roles_privileges
				WHERE	role_id = ?
			";
			query_log($get_privileges_query,array($this->id));
			$rs = $GLOBALS['_database']->Execute($get_privileges_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			app_log($rs->recordCount()." rows returned");
			$privileges = array();
			while(list($id) = $rs->FetchRow()) {
				app_log("Getting privilege $id");
				$privilege = new \Register\Privilege($id);
				array_push($privileges,$privilege);
			}
			return $privileges;
		}
		
		public function has_privilege($param) {
            if (is_numeric($param)) {
                $privilege = new \Register\Privilege($param);
            }
            else {
       			$privilege = new \Register\Privilege();
	    		if (! $privilege->get($param)) {
                    return false;
                }
            }
			$get_privilege_query = "
				SELECT	1
				FROM	register_roles_privileges
				WHERE	role_id = ?
				AND		privilege_id = ?
			";

			$rs = $GLOBALS['_database']->Execute($get_privilege_query,array($this->id,$privilege->id));

			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
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
         * @param $role_name
         */
        public function checkIfUserInRole($user_id, $role_name) {
            $checkIfUserInRole = "
            SELECT * FROM `register_roles` rr
                INNER JOIN register_users_roles rur ON rr.id = rur.role_id
                WHERE rur.user_id = ? AND rr.name = ?;

			";
            $rs = $GLOBALS['_database']->Execute($checkIfUserInRole,array($user_id, $role_name));
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

		public function validName($string) {
			if (preg_match('/^\w[\w\-\_\s]*$/',$string)) return true;
			else return false;
		}
	}
	
