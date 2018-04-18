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
	}
?>
