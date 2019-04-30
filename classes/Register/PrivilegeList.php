<?php
	namespace Register;

	class PrivilegeList {
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT  id
				FROM    register_role_privileges
                WHERE   id = id
			";

            $bind_params = array();
			if (isset($parameters['role_id'])) {
                $find_objects_query .= "
                AND     role_id = ?";
                array_push($bind_params,$parameters['role_id']);
			}
			query_log($find_objects_query);
            $rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
            if (! $rs) {
                $this->_error = "SQL Error in Register::PrivilegeList::find(): ".$GLOBALS['_database']->ErrorMsg();
                return null;
            }

            $objects = array();
            while (list($id) = $rs->FetchRow()) {
                $object = new \Register\Privilege($id);
                array_push($objects,$object);
                $this->_count ++;
            }
            return $objects;
		}

        public function error() {
            return $this->_error;
        }

        public function count() {
            return $this->_count;
        }
	}
