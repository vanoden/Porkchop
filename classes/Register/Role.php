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
			return $this->update($this->id,$parameters);
        }

		public function update($id,$parameters = array()) {
			if (! preg_match('/^\d+$/',$id)) {
				if ($this->id) $id = $this->id;
				else {
					$this->error = "Valid id required in Role::add";
					return null;
				}
			}

			$update_object_query = "
				UPDATE	register_roles
				SET		id = id";

			if ($parameters['description'])
				$update_object_query .= ",
						description = ".$GLOBALS['_database']->qstr($parameters['description'],get_magic_quotes_gpc());

			$GLOBALS['_database']->Execute($update_object_query);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in Role::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			return $this->details($id);
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
				return 0;
			}
			list($this->id) = $rs->FetchRow();
			return $this->details($this->id);
		}
		public function members($id) {
			$get_members_query = "
				SELECT	user_id
				FROM	register_users_roles
				WHERE	role_id = ".$GLOBALS['_database']->qstr($id,get_magic_quotes_gpc());
			$rs = $GLOBALS['_database']->Execute($get_members_query);
			if (! $rs)
			{
				$this->error = "SQL Error in register::role::members: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$admins = array();
			while (list($admin_id) = $rs->FetchRow())
			{
				$_admin = new Admin();
				$admin = $_admin->details($admin_id);
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

		public function notify($code,$message) {
			require_once(MODULES."/email/_classes/default.php");
			$role = $this->get($code);
			if (! $role->id)
			{
				$this->error = "Role not found";
				return null;
			}
			$members = $this->members($role->id);
			foreach ($members as $member)
			{
				$_member = new RegisterPerson();
				$_member->notify($member->id,$message);
			}
		}
	}
?>