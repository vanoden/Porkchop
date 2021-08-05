<?php
	namespace Register;
	class Role {
		public $id;
		public $name;
		public $description;
		public $error;

		public function __construct($id = 0) {
			if (isset($id)) {
				$this->id = $id;
				$this->details();
			}
		}

        public function add($parameters = array()) {
            if (! preg_match('/^[\w\-\_\s]+$/',$parameters['name'])) {
                $this->error = "Failed to add role, invalid name";
                return null;
            }
			$current_role = new Role();
			$current_role->get($parameters['name']);
			if ($current_role->id) {
				$this->error = "Role already exists";
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
                $this->error = "SQL Error in Role::add: ".$GLOBALS['_database']->ErrorMsg();
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
			if (isset($parameters['description']))
				$update_object_query .= ",
						description = ?";
			array_push($bind_params,$parameters['description']);

			$update_object_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);

			$GLOBALS['_database']->Execute($update_object_query,$bind_params);

			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Role::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details();
		}
		public function get($code) {
			$get_object_query = "
				SELECT	id
				FROM	register_roles
				WHERE	name = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->error = "SQL Error in RegisterRole::get: ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->id) = $rs->FetchRow();
			if ($this->id < 1) {
				return false;
			}
			return $this->details();
		}
		public function members() {
			$get_members_query = "
				SELECT	user_id
				FROM	register_users_roles
				WHERE	role_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_members_query,array($this->id));
			if (! $rs) {
				$this->error = "SQL Error in register::role::members: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
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
				$this->error = "SQL Error in Register::Role::hasMember(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($found) = $rs->FetchRow();
			if ($found == 1) return true;
			else return false;
		}
		public function addMember($person_id) {
			if ($this->hasMember($person_id)) {
				$this->error = "Person already has role";
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
				$this->error = "SQL Error in Register::Role::addMember(): ".$GLOBALS['_database']->ErrorMsg();
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
				$this->error = "SQL Error in RegisterRole::details: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			if ($object = $rs->FetchRow()){
				$this->name = $object['name'];
				$this->description = $object['description'];
				return 1;
			}
			else {
				return 0;
			}
		}

		public function notify($message) {
			if (! $this->id) {
				$this->error = "Role not found";
				return null;
			}
			$members = $this->members();
			foreach ($members as $member) {
				$member = new \Register\Person($member->id);
				app_log("Sending notification to '".$member->code."' about contact form",'debug',__FILE__,__LINE__);
				$member->notify($message);
				if ($member->error) {
					app_log("Error sending notification: ".$member->error,'error',__FILE__,__LINE__);
					$this->error = "Failed to send notification: ".$member->error;
					return false;
				}
			}
		}
		public function addPrivilege($new_privilege) {
            if (is_numeric($new_privilege))
                $privilege = new \Register\Privilege($new_privilege);
            else {
    			$privilege = new \Register\Privilege();
			    if (! $privilege->get(array('privilege' => $new_privilege))) {
                    $this->error = "Can't get privilege $new_privilege";
                    return false;
                }
            }
			$add_privilege_query = "
				INSERT	INTO	register_roles_privileges
				VALUES  (?,?)
			";
			$GLOBALS['_database']->Execute($add_privilege_query,array($this->id,$privilege->id));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Register::Role::addPrivilege(): ".$GLOBALS['_database']->ErrorMsg();
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
                $this->error = "SQL Error in Register::Role::dropPrivilege(): ".$GLOBALS['_database']->ErrorMsg();
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
			$rs = $GLOBALS['_database']->Execute($get_privilege_query,array($this->id,$param));

			if (! $rs) {
				$this->error = $GLOBALS['_database']->ErrorMsg();
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
	}
