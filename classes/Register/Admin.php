<?php
	namespace Register;

    class Admin extends Customer {

		public function __construct($id = 0) {

			if ($id) {
				$this->id = $id;
				$this->details();
			}
		}
	
		public function details() {
		    $details = parent::details();
			return;
		}
		
		public function adminUpdate($id,$parameters=array()) {
			parent::update($parameters);
			$bind_params = array();
			if (isset($parameters['department_id'])) {
				$update_admin_query = "
					UPDATE	register_users
					SET		department_id = ?
					WHERE	id = ?";
				array_push($bind_params,$parameters['department_id'],$id);
				$GLOBALS['_database']->Execute($update_admin_query,$bind_params);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in Register::Admin::update(): ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
			return $this->details($id);
		}
    }
