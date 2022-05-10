<?php
	namespace Support\Request;

	class ItemList {
	
		private $_error;
		private $_count;

		public function find($parameters = array()) {
		
			$bind_params = array();

			$find_objects_query = "
				SELECT	s.id
				FROM	support_request_items s
				INNER JOIN support_requests sr ON s.request_id = sr.id
				WHERE	s.id = s.id
			";

            // if search term, then constrain by that
			if ($parameters['searchTerm'] && preg_match('/^\w[\w\-\.\_\s]*$/',$parameters['searchTerm'])) {            
				$find_objects_query .= "
					AND (s.serial_number LIKE '%".$parameters['searchTerm']."%' 
					OR s.description LIKE '%".$parameters['searchTerm']."%') ";
			}

			// if a minimum date is set, constrain on it
			if (isset($parameters['min_date'])) {
			    $minDate = date("Y-m-d H:i:s", strtotime($parameters['min_date']));
			    $find_objects_query .= "
				    AND	sr.date_request > ?
			    ";
				array_push($bind_params,$minDate);
			}

			if (isset($parameters['max_date'])) {
			    $maxDate = date("Y-m-d H:i:s", strtotime($parameters['max_date']));
			    $find_objects_query = "
				    AND	sr.date_request < ?
			    ";
				array_push($bind_params,$maxDate);
			}
			if (isset($parameters['min_date']) && isset($parameters['max_date'])) {
    			$minDate = date("Y-m-d H:i:s", strtotime($parameters['min_date']));
			    $maxDate = date("Y-m-d H:i:s", strtotime($parameters['max_date']));
			    $find_objects_query = "
				    AND sr.date_request < ? AND sr.date_request > ?
			    ";
				array_push($bind_params,$minDate,$maxDate);
			}
			if (isset($parameters['organization_id'])) {
				$find_objects_query .= "
					AND	sr.organization_id = ?";
				array_push($bind_params,$parameters['organization_id']);
			}

			if (!empty($parameters['request_id'])) {
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

			$product = new \Product\Item();
			if (!empty($parameters['product_id'])) {
				$product = new \Product\Item($parameters['product_id']);
				if (!$product->id) return array();
			}
			elseif(!empty($parameters['product_code'])) {
				$product = new \Product\Item();
				if (!$product->get($parameters['product_code'])) return array();
			}

			if ($product->id && !empty($parameters['serial_number'])) {
				$asset = new \Monitor\Asset();
				if (! $asset->get($product->id,$parameters['serial_number'])) return array();
				$find_objects_query .= "
					AND	s.product_id = ?
					AND	s.serial_number = ?";
				array_push($bind_params,$product->id,$parameters['serial_number']);
			}
			elseif (!empty($parameters['serial_number'])) {
				$asset = new \Monitor\Asset();
				if (!$asset->getSimple($parameters['serial_number'])) {
					$this->_error = "Asset not found";
					return false;
				}
				$find_objects_query .= "
					AND	s.serial_number = ?";
				array_push($bind_params,$asset->code);
			}
			
			if (!empty($parameters['customer_id'])) {
				$find_objects_query .= "
					AND	sr.customer_id = ?";
				array_push($bind_params,$parameters['customer_id']);
			}

            // search for unassigned support tickets
			if (isset($parameters['assigned_id']) && empty($parameters['assigned_id'])) {
				$find_objects_query .= "
					AND	s.assigned_id IS NULL";
			}
        
            // search for support tickets by assigned
			if (!empty($parameters['assigned_id'])) {
				$find_objects_query .= "
					AND	s.assigned_id = ?";
				array_push($bind_params,$parameters['assigned_id']);
			}

			if (!empty($parameters['status'])) {
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
				else {
					$find_objects_query .= "
					AND s.status = ?";
					array_push($bind_params,$parameters['status']);
				}
			}

			if (isset($parameters['organization_id'])) {
				$requestList = new \Support\RequestList();
				$requests = $requestList->find(array('organization_id' => $parameters['organization_id']));
				$requestids = array();
				foreach ($requests as $request) {
					array_push($requestids,$request->id);
				}
				$find_objects_query .= "
					AND	s.request_id IN (".join(',',$requestids).")";
			}
			
            // adjust results for sorted by column from ui sortable table
            $sortDirection = 'DESC';
            if (isset($parameters['sort_direction']) && $parameters['sort_direction'] == 'asc') $sortDirection = 'ASC';
			switch ($parameters['sort_by']) {
                case 'requested':
			        $find_objects_query .= "
				        ORDER BY sr.date_request $sortDirection
			        ";
                break;
                case 'requestor':
			        $find_objects_query .= "
				        ORDER BY sr.customer_id $sortDirection
			        ";
                break;
                case 'organization':
			        $find_objects_query .= "
				        ORDER BY sr.organization_id $sortDirection
			        ";
                break;
                case 'product':
			        $find_objects_query .= "
				        ORDER BY s.product_id $sortDirection
			        ";
                break;
                case 'serial':
			        $find_objects_query .= "
				        ORDER BY s.serial_number $sortDirection
			        ";
                break;
                case 'status':
			        $find_objects_query .= "
				        ORDER BY s.status $sortDirection
			        ";
                break;
                case 'ticket_id':
			        $find_objects_query .= "
				        ORDER BY s.id $sortDirection
			        ";
                break;
                default:
                    $find_objects_query .= "
                        ORDER BY s.id $sortDirection
                    ";
                break;
			}
			if (!empty($parameters['_limit']) && preg_match('/^\d+$/',$parameters['_limit'])) {
				$find_objects_query .= "
					LIMIT ".$parameters['_limit'];
			}

			query_log($find_objects_query,$bind_params,true);
			$rs = executeSQLByParams($find_objects_query, $bind_params);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::ItemList::find(): ".$GLOBALS['_database']->ErrorMsg();
				app_log($this->_error,'error');
				return false;
			}
			else {
				app_log($rs->recordCount()." records returned");
			}
			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new \Support\Request\Item($id);
				array_push($objects,$object);
				$this->_count ++;
			}
			return $objects;
		}

		public function last($parameters = array()) {
			$parameters["_limit"] = 1;
			$parameters["sort_by"] = "requested";
			$parameters["sort_direction"] = "DESC";
			return $this->find($parameters);
		}

		/**
		 * get serial numbers available for current support request items
		 */
		public function getSerialNumbersAvailable() {
		
			$find_objects_query = "SELECT DISTINCT(serial_number) FROM support_request_items ORDER BY serial_number ASC";
			$rs = executeSQLByParams($find_objects_query, array());
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
		
			$find_objects_query = "SELECT id, code, name, description FROM product_products WHERE status = 'active' GROUP BY code ORDER BY code ASC";
			$rs = executeSQLByParams($find_objects_query, array());
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::ItemList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while($row = $rs->FetchRow()) if (!empty($row['code'])) $objects[] = array($row['id'], $row['code'], $row['name'], $row['description']);
			return $objects;
		}		

		public function count() {
			return $this->_count;
		}

		public function error() {
			return $this->_error;
		}
	}
