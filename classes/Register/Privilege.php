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
                $this->_error = "SQL Error in Register::Privilege::add(): ".$GLOBALS['_database']->ErrorMsg();
                return false;
            }

            $this->id = $GLOBALS['_database']->Insert_ID();
            return $this->update($parameters);
		}

        public function update($parameters = array()) {
            $update_object_query = "
                UPDATE      register_privileges
                SET         id = id
            ";
            $bind_params = array();

            if ($parameters['name']) {
                $update_object_query .= ",
                name = ?";
                array_push($bind_params,$parameters['name']);
            }

            if ($parameters['description']) {
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
                $this->_error = "SQL Error in Register::Privilege::update(): ".$GLOBALS['_database']->ErrorMsg();
                return false;
            }

            return $this->details();
        }

		public function get($name) {
			$get_object_query = "
				SELECT	id
				FROM	register_privileges
				WHERE	name = ?
			";
			$rs = $GLOBALS['_database']->Execute($get_object_query,array($name));
			if (! $rs) {
				$this->_error = "SQL Error in Register::Privilege::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($id) = $rs->FetchRow();
			if (! $id) return false;
			$this->id = $id;
			return $this->details();
		}

        public function details() {
            $get_object_query = "
                SELECT  id,name,description
                FROM    register_privileges
                WHERE   id = ?
            ";

            $rs = $GLOBALS['_database']->Execute($get_object_query,array($this->id));
            if (! $rs) {
                $this->_error = "SQL Error in Register::Privilege::details(): ".$GLOBALS['_database']->ErrorMsg();
                return false;
            }

            list($this->id,$this->name,$this->description) = $rs->FetchRow();
            return true;
        }

        public function delete() {
			$delete_xref_query = "
				DELETE
				FROM	register_roles_privileges
				WHERE	privilege_id = ?";
			$GLOBALS['_database']->Execute($delete_xref_query,$this->id);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Register::Privilege::delete(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
            $delete_object_query = "
                DELETE
                FROM    register_privileges
                WHERE   id = ?";
            $GLOBALS['_database']->Execute($delete_object_query,$this->id);
            if ($GLOBALS['_database']->ErrorMsg()) {
                $this->_error = "SQL Error in Register::Privilege::delete(): ".$GLOBALS['_database']->ErrorMsg();
                return false;
            }
            return true;
        }

		public function error() {
			return $this->_error;
		}
	}
