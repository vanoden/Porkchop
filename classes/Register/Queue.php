<?php
	namespace Register;

	class Queue {
	
		public $id;
		public $error;
		public $code;
		public $status;
		public $is_reseller;
		public $assigned_reseller_id;
		public $notes;
		
		// business contact fields
		public $name;		
		public $address;
		public $city;
		public $state;
		public $zip;
		public $phone;
		public $cell;

		public function __construct($id = 0) {
		
			// Clear Error Info
			$this->error = '';

			// Database Initialization
			$schema = new Schema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			} elseif (!empty($id)) {
				$this->id = $id;
				$this->details();
			}
		}
		
		// hydrate known details about this queue object from known id if set
		public function details() {
		    if (!empty($this->id)) {
                $get_queued_contacts_query = "
	                SELECT	*
	                FROM	register_queue
	                WHERE	id = " . $this->id;
                $rs = $GLOBALS['_database']->Execute( $get_queued_contacts_query );
                if (! $rs) {
	                $this->error = "SQL Error in Register::ContactList::find(): ".$GLOBALS['_database']->ErrorMsg();
	                return null;
                }
                while ($row = $rs->FetchRow()) {
                    foreach ($row as $rowValueKey => $rowValue){
                        if (!is_numeric($rowValueKey)) $this->$rowValueKey = $rowValue;
                    }
                }
		    }
		}

		public function add($parameters) {
			app_log("Register::Queue::add()",'trace',__FILE__,__LINE__);
			$this->error = null;
			$add_object_query = "
				INSERT
				INTO	register_queue
    				(name, code, date_created, is_reseller, assigned_reseller_id, address, city, state, zip, phone, cell, product_id, serial_number)
				VALUES
	    			(?, ?, sysdate(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )
	    			";

			$rs = $GLOBALS['_database']->Execute(
				$add_object_query,
				array(
    				$parameters['name'],
					$parameters['code'],
					$parameters['is_reseller'],
					$parameters['assigned_reseller_id'],
					$parameters['address'],
					$parameters['city'],
					$parameters['state'],
					$parameters['zip'],
					$parameters['phone'],
					$parameters['cell'],
                    $parameters['product_id'],
                    $parameters['serial_number']
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
