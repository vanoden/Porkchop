<?php
	namespace Register;

	class Privilege Extends \BaseModel {

		public $description;
		public $name;
		public $module;

		public function __construct($id = 0) {
			$this->_tableName = 'register_privileges';
			$this->_tableUKColumn = 'name';
		    parent::__construct($id);
		}

		public function add($parameters = []) {

            $add_object_query = "
                INSERT
                INTO    register_privileges
                (       name)
                VALUES
                (       ? )
            ";

            $GLOBALS['_database']->Execute(
                $add_object_query,
                array($parameters["name"])
            );

            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return false;
            }

            $this->id = $GLOBALS['_database']->Insert_ID();

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

        public function update($parameters = []): bool {

            $update_object_query = "
                UPDATE      register_privileges
                SET         id = id
            ";
            $bind_params = array();

            if (!empty($parameters['name'])) {
                $update_object_query .= ",
                name = ?";
                array_push($bind_params,$parameters['name']);
            }

            if (!empty($parameters['module'])) {
                $update_object_query .= ",
                module = ?";
                array_push($bind_params,$parameters['module']);
            }

            if (!empty($parameters['description'])) {
                $update_object_query .= ",
                description = ?";
                array_push($bind_params,$parameters['privilege']);
            }

            $update_object_query .= "
                WHERE       id = ?
            ";
            array_push($bind_params,$this->id);

            $GLOBALS['_database']->Execute($update_object_query,$bind_params);

            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return false;
            }

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));	

            return $this->details();
        }

        public function delete(): bool {
			$delete_xref_query = "
				DELETE
				FROM	register_roles_privileges
				WHERE	privilege_id = ?";
			$GLOBALS['_database']->Execute($delete_xref_query,$this->id);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return false;
			}
            $delete_object_query = "
                DELETE
                FROM    register_privileges
                WHERE   id = ?";
            $GLOBALS['_database']->Execute($delete_object_query,$this->id);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->SQLError($GLOBALS['_database']->ErrorMsg());
                return false;
            }

            // audit the delete event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Deleted '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'delete'
            ));

            return true;
        }

		public function peers() {
			$get_object_query = "
				SELECT	rur.user_id
				FROM	register_users_roles rur,
						register_roles_privileges rrp
				WHERE	rrp.privilege_id = ?
				AND		rrp.role_id = rur.role_id
			";
            $rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
			if (! $rs) {
				$this->SQLError($GLOBALS['_database']->ErrorMsg());
				return null;
			}
			$people = array();
			while (list($id) = $rs->FetchRow()) {
				$person = new \Register\Person($id);
				array_push($people,$person);
			}
			return $people;
		}

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
	}
