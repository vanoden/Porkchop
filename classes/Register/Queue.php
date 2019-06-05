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
		public $possibleOrganizationStatus = array('NEW','ACTIVE','EXPIRED','HIDDEN','DELETED');

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

		    // if they've found an existing organization
		    if($_REQUEST['organization']) $this->name = $_REQUEST['organization'];
		
            // process the new or existing queued customer to the chosen status
            global $_config;
            $registerOrganizationList = new \Register\OrganizationList();          
            $existingOrganization = $registerOrganizationList->find(array('name' => $this->name, 'status' => $this->possibleOrganizationStatus));
            $organizationExists = !empty($existingOrganization);
            $registerOrganization = new \Register\Organization();
                        
            // set to active - doesn't exist yet - create the organization
            if (!$organizationExists) {
                $newOrganizationDetails = $registerOrganization->addQueued(array('name' => $this->name, 'code' => $this->code, 'status' => 'NEW', 'is_reseller' => $this->is_reseller, 'assigned_reseller_id' => $this->reseller, 'notes' => $this->notes));
            } else {
                //  already exists - set to approved - update the organization
                $registerOrganization->get($this->code);
                $registerOrganization->update(array('status' => 'APPROVED', 'notes' => $this->notes));
            }
            $existingOrganization = array_pop($registerOrganizationList->find(array('name' => $this->name)));
            
            // update to have the queued login match the 'approved' organization
            $registerCustomer = new \Register\Customer($this->register_user_id);
            $registerCustomer->update(array('organization_id' => $existingOrganization->id));
            
            // add a support_request record with customer id, date entry and request items for each item entered. 
            $supportRequest = new \Support\Request();
			$newSupportRequest = $supportRequest->add(
				array(
					"date_request"	    => date("Y-m-d H:i:s"),
					"customer_id"	    => $this->register_user_id,
					"organization_id"   => $existingOrganization->id,
					"type"			    => 'service',
					"status"		    => "NEW"
				)
			);
	
	        // add production		
			if (!empty($this->product_id)) {
                $item = array (
                    'line'			=> 1,
                    'product_id'    => $this->product_id,
                    'description'	=> "New organization approved with registered device. [" . $existingOrganization->name . "]",
                    'quantity'		=> 1
                );
                if (!empty($this->serial_number)) $item['serial_number'] = $this->serial_number;
                $supportRequest->addItem($item);
			}

            // get contact work email address
            $registerContact = new \Register\Contact();
            $registerContact->detailsByUserByTypeByDesc($registerCustomer->id, 'email');
            $contactWorkEmail = $registerContact->value;

            // email notification must be sent to members of the 'support user' role
            $emailNotification = new \Email\Notification(
            array('subject' => 'New Customer Approved', 
                  'template' => TEMPLATES . '/registration/admin_notification_new_customer.html', 
                  'templateVars' => array('USERDETAILS' => $registerContact->person->first_name . " " . $registerContact->person->last_name . " - " . $registerOrganization->name, 'URL' => 'https://'. $_config->site->hostname . '_support/requests')
                  )
            );  
            $emailNotification->send('support@spectrosinstruments.com', 'no-reply@spectrosinstruments.com');                    	                       

            // alert 'support user' users of the new customer
            $message = new \Email\Message(
                array(
                    'from'	=> 'service@spectrosinstruments.com',
                    'subject'	=> 'New Customer Approved',
                    'body'		=> $emailNotification->getMessageBody()
                )
            );
            $message->html(true);

            $role = new \Register\Role();
            $role->get('support user');
            $role->notify($message);
            if ($role->error) app_log("Error sending admin new customer reminder: ".$role->error);	

            // An email confirmation must be sent to the customer
            $emailNotification = new \Email\Notification(
            array('subject' => 'Your account has been Approved!', 
                  'template' => TEMPLATES . '/registration/welcome.html', 
                  'templateVars' => array('USERDETAILS' => $registerContact->person->first_name . " " . $registerContact->person->last_name, 'URL' => 'https://'. $_config->site->hostname)
                  )
            );
            $isEmailSent = $emailNotification->send($contactWorkEmail, 'no-reply@spectrosinstruments.com');
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
