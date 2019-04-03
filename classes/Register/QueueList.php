<?php
	namespace Register;
	
	class QueueList {
	
		public $error;
		public $count;
		
		public function find($parameters = array()) {
		
			$get_queued_contacts_query = "
				SELECT	id
				FROM	register_queue
				WHERE	id = id
			";
			
	        if (!empty($parameters['name']))
	            $get_queued_contacts_query .= " AND	" . $this->columnSearch($parameters['name'], array('name'));
	            
	        if (!empty($parameters['address']))
	            $get_queued_contacts_query .= " AND	" . $this->columnSearch($parameters['address'], array('address', 'city', 'state', 'zip'));
	            
	        if (!empty($parameters['phone']))
	            $get_queued_contacts_query .= " AND	" . $this->columnSearch($parameters['phone'], array('phone', 'cell'));	    
	                
	        if (!empty($parameters['code']))
	            $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['code'], array('code'));
	            
            if (!empty($parameters['status']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['status'], array('status'));
                
            if (!empty($parameters['is_reseller']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['is_reseller'], array('is_reseller'));

            if (!empty($parameters['assigned_reseller_id']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['assigned_reseller_id'], array('assigned_reseller_id'));

            if (!empty($parameters['product_id']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['product_id'], array('product_id'));
                
            if (!empty($parameters['serial_number']))
                $get_queued_contacts_query .= " AND	" . $this->columnExact($parameters['serial_number'], array('serial_number'));

            // @TODO date_created
	        // print $get_queued_contacts_query;
	        
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
