<?php
	namespace Register;

	class Privilege {
		public $id;
		private $_error;

		public function __construct($id = 0) {
			if (is_numeric($id) && $id > 0) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters = array()) {
			if ($parameters['role_id']) {
				$role = new \Register\Role($parameters['role_id']);
				if (! $role->id) {
					$this->_error = "Role not found";
					return false;
				}
			}
			else {
				$this->_error = "Role id required";
				return false;
			}

            $add_object_query = "
                INSERT
                INTO    register_role_privileges
                (       role_id,privilege)
                VALUES
                (       ?,? )
            ";

            $GLOBALS['_database']->Execute(
                $add_object_query,
                array($role->id,$parameters["privilege"])
            );

            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->_error = "SQL Error in Register::Privilege::add(): ".$GLOBALS['_database']->ErrorMsg();
                return false;
            }

            $this->id = $GLOBALS['_database']->Insert_ID();
            return $this->update($parameters);
		}

        public function update($parameters = array()) {
            $update_object_query = "
                UPDATE      register_role_privileges
                SET         id = id
            ";
            $bind_params = array();

            if ($parameters['privilege']) {
                $update_object_query .= ",
                privilege = ?";
                array_push($bind_params,$parameters['privilege']);
            }

            $update_object_query .= "
                WHERE       id = ?
            ";
            array_push($bind_params,$this->id);

            $GLOBALS['_database']->Execute($update_object_query,$bind_params);

            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->_error = "SQL Error in Register::Privilege::update(): ".$GLOBALS['_database']->ErrorMsg();
                return false;
            }

            return $this->details();
        }

        public function details() {
            $get_object_query = "
                SELECT  role_id,privilege
                FROM    register_role_privileges
                WHERE   id = ?
            ";

            $rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
            if (! $rs) {
                $this->_error = "SQL Error in Register::Privilege::details(): ".$GLOBALS['_databse']->ErrorMsg();
                return false;
            }

            list($this->role_id,$this->privilege) = $rs->FetchRow();
            $this->role = new \Register\Role($this->role_id);
            return true;
        }

		public function error() {
			return $this->_error;
		}
	}
?>