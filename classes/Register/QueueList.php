<?php
	namespace Register;
	
	class QueueList {
	
		public $error;
		public $count;
		public $possibleStatus = array('VERIFYING','PENDING','APPROVED','DENIED');
		
		public function find($parameters = array()) {
		
			$get_queued_contacts_query = "
				SELECT	id
				FROM	register_queue
				WHERE	id = id
			";
			
            if (!empty($parameters['searchAll']))
	            $get_queued_contacts_query .= " AND	" . $this->columnSearch($parameters['searchAll'], array('name', 'address', 'city', 'state', 'zip', 'phone', 'cell', 'code', 'notes', 'product_id', 'serial_number'));			
			
	        if (!empty($parameters['name']))
	            $get_queued_contacts_query .= " AND	" . $this->columnSearch($parameters['name'], array('name'));
	            
	        if (!empty($parameters['address']))
	            $get_queued_contacts_query .= " AND	" . $this->columnSearch($parameters['address'], array('address', 'city', 'state', 'zip'));
	            
	        if (!empty($parameters['phone']))
	            $get_queued_contacts_query .= " AND	" . $this->columnSearch($parameters['phone'], array('phone', 'cell'));	    
	                
	        if (!empty($parameters['code']))
	            $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['code'], array('code'));
                
            if (!empty($parameters['is_reseller']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['is_reseller'], array('is_reseller'));

            if (!empty($parameters['assigned_reseller_id']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['assigned_reseller_id'], array('assigned_reseller_id'));

            if (!empty($parameters['product_id']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['product_id'], array('product_id'));
                
            if (!empty($parameters['serial_number']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['serial_number'], array('serial_number'));

            if (!empty($parameters['status'])) {
                $get_queued_contacts_query .= "AND (";
                foreach ($parameters['status'] as $status) $get_queued_contacts_query .= " OR " . $this->columnExact($status, array('status'));         
                $get_queued_contacts_query .= ")";
                $get_queued_contacts_query  = str_replace ( "( OR (" , "((" , $get_queued_contacts_query); // @TODO, this isn't the best really to produce the OR statements
            }

            if (!empty($parameters['dateStart'])) 
                $get_queued_contacts_query .= " AND	`date_created` > '" . date("Y-m-d H:i:s", strtotime($parameters['dateStart'])) . "'";

            if (!empty($parameters['dateEnd'])) 
                $get_queued_contacts_query .= " AND	`date_created` < '" . date("Y-m-d H:i:s", strtotime($parameters['dateEnd'])) . "'";

			$rs = $GLOBALS['_database']->Execute( $get_queued_contacts_query );
			if (! $rs) {
				$this->error = "SQL Error in Register::ContactList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			// get list of contacts for UI
			$queuedContacts = array();
			while (list($id) = $rs->FetchRow()) {
				$contact = new \Register\Queue($id);
				array_push($queuedContacts,$contact);
			}
			return $queuedContacts;
		}
		
		// @TODO make this global
		public function sanatizeInput($value) {
		    return $GLOBALS['_database']->qstr($value,get_magic_quotes_gpc);
		}
		
		// @TODO make this global
		public function sanatizeInputSearch($value) {
		    return preg_replace("/'$/","",preg_replace("/^'/","",$GLOBALS['_database']->qstr($value,get_magic_quotes_gpc)));
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
