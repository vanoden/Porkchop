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
		public $register_user_id;
		
		// business contact fields
		public $name;		
		public $address;
		public $city;
		public $state;
		public $zip;
		public $phone;
		public $cell;
		public $possibleStatus = array('VERIFYING','PENDING','APPROVED','DENIED');

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
		
		public function getByQueuedLogin($queuedUserId = 0) {
		
		    if (!empty($queuedUserId)) {

                $get_queued_contacts_query = "
	                SELECT	*
	                FROM	register_queue
	                WHERE	register_user_id = " . $queuedUserId;
	                
                $rs = $GLOBALS['_database']->Execute( $get_queued_contacts_query );
                if (! $rs) {
	                $this->error = "SQL Error in Register::Queue::getByQueuedLogin(): ".$GLOBALS['_database']->ErrorMsg();
	                return null;
                }
                while ($row = $rs->FetchRow()) {
                    foreach ($row as $rowValueKey => $rowValue){
                        if (!is_numeric($rowValueKey)) $this->$rowValueKey = $rowValue;
                    }
                }
		    }
		}
		
        /**
         * update potential customer
         * @param array $parameters
         */
		public function update ($parameters) {
		
			if (! preg_match('/^[0-9]+$/',$this->id)) {
				$this->error = "ID Required for update method.";
				return 0;
			}

			$bind_params = array();
			$update_contact_query = " UPDATE register_queue SET id = id";

			if (isset($parameters['notes'])) {
				$update_contact_query .= ",
						notes = ?";
				array_push($bind_params,$parameters['notes']);
			}

			if (isset($parameters['status'])) {
                if (!in_array($parameters['status'], $this->possibleStatus)) {
				    $this->error = "Invalid Status for RegisterQueue entry";
				    return 0;
                }
				$update_contact_query .= ",
						status = ?";
				array_push($bind_params,$parameters['status']);
			}

			$update_contact_query .= "
				WHERE	id = ?";
			array_push($bind_params,$this->id);
			query_log($update_contact_query);
			$GLOBALS['_database']->Execute($update_contact_query,$bind_params);
			
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegisterQueue::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
						
			// return basic queue entry details
			return $this->details();
		}
		
        /**
         * sync the live account that may or may not be associated with the queued account being edited
         */
		public function syncLiveAccount () {

            // process the new or existing queued customer to the chosen status  
            $registerOrganizationList = new \Register\OrganizationList();          
            $existingOrganization = $registerOrganizationList->find(array('name' => $this->name, 'status' => $this->possibleStatus));
            $organizationExists = !empty($existingOrganization);
            $registerOrganization = new \Register\Organization();
            
            // set to active - doesn't exist yet - create the organization
            if (!$organizationExists) {
                $newOrganizationDetails = $registerOrganization->addQueued(array('name' => $this->name, 'code' => $this->code, 'status' => 'APPROVED', 'is_reseller' => $this->is_reseller, 'assigned_reseller_id' => $this->reseller, 'notes' => $this->notes));
            } else {
                // set to active - already exists - update the organization
                $registerOrganization->get($this->code);
                $registerOrganization->update(array('status' => 'APPROVED', 'notes' => $this->notes));
            }
            $existingOrganization = $registerOrganizationList->find(array('name' => $this->name));
            
            // update to have the queued login match the 'approved' organization
            $registerCustomer = new \Register\Customer($this->register_user_id);
            $registerCustomer->update(array('organization_id' => $existingOrganization[0]->id));
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
	                $this->error = "SQL Error in Register::Queue::details(): ".$GLOBALS['_database']->ErrorMsg();
	                return null;
                }
                while ($row = $rs->FetchRow()) {
                    foreach ($row as $rowValueKey => $rowValue){
                        if (!is_numeric($rowValueKey)) $this->$rowValueKey = $rowValue;
                    }
                }
		    }
		}

        /**
         * add new potential customer
         * @param array $parameters
         */
		public function add($parameters) {		
			app_log("Register::Queue::add()",'trace',__FILE__,__LINE__);
			$this->error = null;
			$add_object_query = "
				INSERT
				INTO	register_queue
    				(name, code, date_created, is_reseller, assigned_reseller_id, address, city, state, zip, phone, cell, product_id, serial_number, register_user_id)
				VALUES
	    			(?, ?, sysdate(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	    			";

            // zero out empty values for int DB fields
            if (empty($parameters['is_reseller'])) $parameters['is_reseller'] = 0;
            if (empty($parameters['assigned_reseller_id'])) $parameters['assigned_reseller_id'] = 0;
            if (empty($parameters['product_id'])) $parameters['product_id'] = 0;
            if (empty($parameters['register_user_id'])) $parameters['register_user_id'] = NULL;

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
                    $parameters['serial_number'],
                    $parameters['register_user_id']
				)
			);
			if (! $rs) {
				$this->error = "SQL Error in \Register\Queue::add: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$this->id = $GLOBALS['_database']->Insert_ID();
			return $this->id;
		}
    }
