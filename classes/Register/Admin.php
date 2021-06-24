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
			if (isset($parameters['department_id'])) {
				$update_admin_query = "
					UPDATE	register_users
					SET		department_id = ".$GLOBALS['_database']->qstr($parameters['department_id'],get_magic_quotes_gpc())."
					WHERE	id = ".$GLOBALS['_database']->qstr($id,get_magic_quotes_gpc());
				$GLOBALS['_database']->Execute($update_admin_query);
				if ($GLOBALS['_database']->ErrorMsg()) {
					$this->error = "SQL Error in register::admin::update: ".$GLOBALS['_database']->ErrorMsg();
					return 0;
				}
			}
			return $this->details($id);
		}
    }
