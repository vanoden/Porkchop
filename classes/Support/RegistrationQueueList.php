<?php
	namespace Support;
	
	class RegistrationQueueList {
	
		public $error;
		public $count;
        public $possibleStatus = array('PENDING','APPROVED','DENIED');

		public function find($parameters = array()) {
		
			$get_queued_registration_query = "
				SELECT	id
				FROM	product_registration_queue
				WHERE	id = id
			";

            if (!empty($parameters['dateStart'])) $get_queued_registration_query .= " AND	`date_purchased` > '" . date("Y-m-d H:i:s", strtotime($parameters['dateStart'])) . "'";

            if (!empty($parameters['dateEnd']))  $get_queued_registration_query .= " AND	`date_purchased` < '" . date("Y-m-d H:i:s", strtotime($parameters['dateEnd'])) . "'";

            if (!empty($parameters['status'])) {
                $get_queued_registration_query .= "AND (";
                foreach ($parameters['status'] as $status) $get_queued_registration_query .= " OR " . $this->columnExact($status, array('status'));         
                $get_queued_registration_query .= ")";
                $get_queued_registration_query  = str_replace ( "( OR (" , "((" , $get_queued_registration_query); // @TODO, this isn't the best really to produce the OR statements
            }
            
            // if search term, then constrain by that
            if ($parameters['searchTerm']) {            
                $get_queued_registration_query = "
                SELECT	prq.id
                FROM	product_registration_queue prq
                LEFT JOIN register_users ru ON prq.customer_id = ru.id
                LEFT JOIN register_organizations ro ON ru.organization_id = ro.id
                    WHERE 
                    prq.serial_number LIKE '%".$parameters['searchTerm']."%' 
                    OR ru.first_name LIKE '%".$parameters['searchTerm']."%'
                    OR ru.last_name LIKE '%".$parameters['searchTerm']."%'
                    OR ru.login LIKE '%".$parameters['searchTerm']."%'
                    OR ro.name LIKE '%".$parameters['searchTerm']."%'    
                    OR prq.distributor_name LIKE '%".$parameters['searchTerm']."%'  
                    OR prq.notes LIKE '%".$parameters['searchTerm']."%' ";
            }

            $rs = executeSQLByParams($get_queued_registration_query, array());		
			if (! $rs) {
				$this->error = "SQL Error in RegistrationQueueList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			// get list of customer registrations for UI
			$queuedProductRegistrations = array();
			while (list($id) = $rs->FetchRow()) {
				$registration = new \Support\RegistrationQueue($id);
				array_push($queuedProductRegistrations,$registration);
			}
			return $queuedProductRegistrations;
		}

		// @TODO make this global
		public function sanatizeInput($value) {
		    return $GLOBALS['_database']->qstr($value,get_magic_quotes_gpc());
		}
		
		// @TODO make this global
		public function sanatizeInputSearch($value) {
		    return preg_replace("/'$/","",preg_replace("/^'/","",$GLOBALS['_database']->qstr($value,get_magic_quotes_gpc())));
		}
		
		// @TODO make this global
		public function columnExact($value, $columnNames) {
		
		    $compareSQL = "(";
		    foreach ($columnNames as $column) $compareSQL .= " `" . $column . "` = '" . $this->sanatizeInputSearch($value) . "' OR ";
		    
		    // remove trailing OR and apply closing parens
		    $compareSQL = substr ( $compareSQL , 0, -3 ); 
		    $compareSQL = $compareSQL . ")";
		    return $compareSQL;
		}
		
		// @TODO make this global
		public function columnSearch($value, $columnNames) {
		    
		    $compareSQL = "(";
		    foreach ($columnNames as $column) $compareSQL .= " `" . $column . "` LIKE '%" . $this->sanatizeInputSearch($value) . "%' OR ";
		    
		    // remove trailing OR and apply closing parens
		    $compareSQL = substr ( $compareSQL , 0, -3 ); 
		    $compareSQL = $compareSQL . ")";
		    return $compareSQL;
		}	
	}
