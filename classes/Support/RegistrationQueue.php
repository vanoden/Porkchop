<?php
	namespace Support;

	class RegistrationQueue {
	
	    public $id;
	    public $customer_id;
	    public $product_id;
	    public $serial_number;
	    public $date_purchased;
	    public $distributor_name;	
	    public $error;
	    
		/**
		 * construct RegistrationQueue
		 * @param int $id
		 */
		public function __construct($id = 0) {

			// clear error info
			$this->error = '';

			// database initialization
			$schema = new Schema();
			if ($schema->error) {
				$this->error = "Failed to initialize schema: ".$schema->error;
			} elseif (!empty($id)) {
				$this->id = $id;
				$this->details();
			}
		}
		
		/**
		 * get potential product registrations for a customer by id
		 * @param int $customerId
		 */
		public function getByCustomer($customerId = 0) {
		    if (!empty($customerId)) {
                $get_queued_contacts_query = "
	                SELECT	`id`
	                FROM	`product_registration_queue`
	                WHERE	`customer_id` = " . $customerId;
	                
                $rs = $GLOBALS['_database']->Execute( $get_queued_contacts_query );
                if (! $rs) {
	                $this->error = "SQL Error in RegistrationQueue::getByCustomer(): ".$GLOBALS['_database']->ErrorMsg();
	                return null;
                }
                list($id) = $rs->FetchRow();
				$this->id = $id;
				return $this->details();
		    }
		}
		
		/**
		 * hydrate known details about this queue object from known id if set
		 */
		public function details() {
		    if (!empty($this->id)) {
                $get_queued_registration_query = "
	                SELECT	*
	                FROM	`product_registration_queue`
	                WHERE	id = " . $this->id;
                $rs = $GLOBALS['_database']->Execute( $get_queued_registration_query );
                if (! $rs) {
	                $this->error = "SQL Error in RegistrationQueue::details(): ".$GLOBALS['_database']->ErrorMsg();
	                return null;
                }
                while ($row = $rs->FetchRow()) {
                    foreach ($row as $rowValueKey => $rowValue){
                        if (!is_numeric($rowValueKey)) $this->$rowValueKey = $rowValue;
                    }
                }
				return true;
		    }
		}
		
        /**
         * update potential registration
         * @param array $parameters
         */
		public function update($parameters) {
		
			if (! preg_match('/^[0-9]+$/',$this->id)) {
				$this->error = "ID Required for update method.";
				return 0;
			}
			$bind_params = array();
			$update_registration_query = "UPDATE product_registration_queue SET id = id";

            // push any allowed parameter values on to the update query
            $updateableParams = array('customer_id','product_id','serial_number','distributor_name');
            foreach ($updateableParams as $keyName) {
                if (isset($parameters[$keyName])) {
                    $update_registration_query .= ",
		                    $keyName = ?";
                    array_push($bind_params,$parameters[$keyName]);
                }
            }
			$update_registration_query .= "
				WHERE	id = ?";
				
            array_push($bind_params,$this->id);
            query_log($update_registration_query);
            $GLOBALS['_database']->Execute($update_registration_query,$bind_params);
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->error = "SQL Error in RegistrationQueue::update: ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
						
			// return basic queue entry details
			return $this->details();
		}

        /**
         * add new potential registration
         * @param array $parameters
         */
		public function add($parameters) {

			app_log("RegistrationQueue::add()",'trace',__FILE__,__LINE__);
			$this->error = null;

            // check required fields are populated for the insert
            if (!isset($parameters['customer_id'])) {
                $this->error = "Customer Id is required";
                return false;
            }
            if (!isset($parameters['product_id'])) {
                $this->error = "Product Id is required";
                return false;
            }
        
            // build query
			$add_object_query = "
				INSERT INTO	`product_registration_queue`
    				(`customer_id`, `product_id`, `serial_number`, `date_purchased`, `distributor_name`)
				VALUES
	    			(?, ?, ?, ?, ?)";

            // zero out empty values for int DB fields
            if (!empty($parameters['date_purchased'])) {
                $parameters['date_purchased'] = date("Y-m-d H:i:s", strtotime($parameters['date_purchased']));
            } else {
                $parameters['date_purchased'] = date("Y-m-d H:i:s", time());
            }
            if (empty($parameters['distributor_name'])) $parameters['distributor_name'] = NULL;
            if (empty($parameters['serial_number'])) $parameters['serial_number'] = NULL;
			$rs = $GLOBALS['_database']->Execute(
				$add_object_query,
				array(
    				$parameters['customer_id'],
					$parameters['product_id'],
					$parameters['serial_number'],
					$parameters['date_purchased'],
					$parameters['distributor_name'],
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
