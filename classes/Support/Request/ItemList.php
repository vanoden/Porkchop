<?php
	namespace Support\Request;

	class ItemList {
	
		private $_error;
		private $_count;

		public function find($parameters = array()) {		

			$find_objects_query = "
				SELECT	s.id
				FROM	support_request_items s
				WHERE	s.id = s.id
			";

            // if search term, then constrain by that
            if ($parameters['searchTerm']) {            
                $find_objects_query = "
                SELECT	s.id
                FROM    support_request_items s
                    WHERE s.serial_number LIKE '%".$parameters['searchTerm']."%' 
                    OR s.description LIKE '%".$parameters['searchTerm']."%' ";
            }
            
			// if a minimum date is set, constrain on it
			if (isset($parameters['min_date'])) {
			
			    $minDate = date("Y-m-d H:i:s", strtotime($parameters['min_date']));
			    $find_objects_query = "
				    SELECT	s.id
				    FROM	support_request_items s
				    INNER JOIN support_requests sr ON s.request_id = sr.id
				    WHERE	sr.date_request > '$minDate'
			    ";
			}            

			$bind_params = array();
			if (isset($parameters['request_id'])) {
				$request = new \Support\Request($parameters['request_id']);
				if ($request->error()) {
					$this->_error = $request->error();
					return false;
				}
				if (! $request->id) {
					$this->_error = "Request not found";
					return false;
				}
				$find_objects_query .= "
				AND s.request_id = ?";
				array_push($bind_params,$request->id);
			}
			if (isset($parameters['product_id'])) {
				$find_objects_query .= "
					AND	s.product_id = ?";
				array_push($bind_params,$parameters['product_id']);
			}
			if (isset($parameters['serial_number'])) {
				$find_objects_query .= "
					AND	s.serial_number = ?";
				array_push($bind_params,$parameters['serial_number']);
			}

			if (isset($parameters['status']) && !empty($parameters['status'])) {
				if (is_array($parameters['status'])) {

					$find_objects_query .= "
					AND	s.status IN (";
					$started = 0;
					foreach ($parameters['status'] as $status) {
						if (! in_array($status,array('NEW','ACTIVE','PENDING_VENDOR','PENDING_CUSTOMER','COMPLETE','CLOSED'))) {
							$this->_error = "Invalid status '$status'";
							return false;
						}
						if ($started) $find_objects_query .= ",";
						$find_objects_query .= "'$status'";
						$started = 1;
					}
					$find_objects_query .= ")";
				}
				
			    if (preg_match('/^[\w\s]+$/',$parameters['status'][0])) {
				    $find_objects_query .= "\tAND s.status = ?";
				    array_push($bind_params,$parameters['status'][0]);
			    }
			}
			
			$find_objects_query .= "
				ORDER BY s.id DESC
			";
			query_log($find_objects_query);
			$rs = $GLOBALS['_database']->Execute($find_objects_query,$bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::ItemList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new \Support\Request\Item($id);
				array_push($objects,$object);
				$this->_count ++;
			}
			return $objects;
		}

		/**
		 * get serial numbers available for current support request items
		 */
		public function getSerialNumbersAvailable() {
		
			$find_objects_query .= "SELECT DISTINCT(serial_number) FROM support_request_items ORDER BY serial_number ASC";
			query_log($find_objects_query);
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::ItemList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while($row = $rs->FetchRow()) if (!empty($row['serial_number'])) $objects[] = $row['serial_number'];
			return $objects;
		}

		/**
		 * get serial numbers available for current support request items
		 */
		public function getProductsAvailable() {
		
			$find_objects_query .= "SELECT id, code FROM product_products GROUP BY code ORDER BY code ASC";
			query_log($find_objects_query);
			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::ItemList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while($row = $rs->FetchRow()) if (!empty($row['code'])) $objects[] = array($row['id'], $row['code']);
			return $objects;
		}		

		public function count() {
			return $this->_count;
		}

		public function error() {
			return $this->_error;
		}
	}
