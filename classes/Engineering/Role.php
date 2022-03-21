<?php
	namespace Engineering;
	
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
			$current_role = new \Engineering\Role();
			$current_role->get($parameters['name']);
			if ($current_role->id) {
				$this->error = "Role already exists";
				return false;
			}

            $add_object_query = "
                INSERT
                INTO    engineering_roles
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
				UPDATE	engineering_roles
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
				FROM	engineering_roles
				WHERE	name = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($code)
			);
			if (! $rs) {
				$this->error = "SQL Error in EngineeringRole::get: ".$GLOBALS['_database']->ErrorMsg();
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
				FROM	engineering_users_roles
				WHERE	role_id = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_members_query,array($this->id));
			if (! $rs) {
				$this->error = "SQL Error in engineering::role::members: ".$GLOBALS['_database']->ErrorMsg();
				return 0;
			}
			$users = array();
			while (list($user_id) = $rs->FetchRow()) {
				array_push($users,$user_id);
			}
			return $users;
		}
		public function hasMember($person_id) {
			$get_member_query = "
				SELECT	1
				FROM	engineering_users_roles
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
				$this->error = "SQL Error in Engineering::Role::hasMember(): ".$GLOBALS['_database']->ErrorMsg();
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
				INTO	engineering_users_roles
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
				$this->error = "SQL Error in Engineering::Role::addMember(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			return true;
		}
		public function details() {
			$get_object_query = "
				SELECT	id,
						name,
						description
				FROM	engineering_roles
				WHERE	id = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($this->id)
			);
			if (! $rs) {
				$this->error = "SQL Error in EngineeringRole::details: ".$GLOBALS['_database']->ErrorMsg();
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
	}
