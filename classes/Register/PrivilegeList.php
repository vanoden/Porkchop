<?php
	namespace Register;

	class PrivilegeList {
		private $_count = 0;
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT  rp.id
				FROM    register_privileges rp
				WHERE   id = id
			";

            $bind_params = array();

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
