<?php
	namespace Register;

	class Queue {
	
		public $id;
		public $error;
		public $name;
		public $code;
		public $status;
		public $is_reseller;
		public $assigned_reseller_id;
		public $notes;

		public function __construct($id = 0) {
		
			// Clear Error Info
			$this->error = '';

			// Database Initialization
			$schema = new Schema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			} elseif ($id) {
				$this->id = $id;
				$this->details();
			}
		}

		public function add($parameters) {
		
			app_log("Register::Queue::add()",'trace',__FILE__,__LINE__);
			$this->error = null;
			$add_object_query = "
				INSERT
				INTO	register_queue
    				(name, code, date_created, is_reseller, assigned_reseller_id)
				VALUES
	    			(?,?,sysdate(),?,?) ";

			$rs = $GLOBALS['_database']->Execute(
				$add_object_query,
				array(
    				$parameters['name'],
					$parameters['code'],
					$parameters['is_reseller'],
					$parameters['assigned_reseller_id']
				)
			);
			if (! $rs) {
				$this->error = "SQL Error in \Register\Organization::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->id;
		}
    }
