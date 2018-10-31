<?
	namespace Support\Request;

	class ItemList {
		private $_error;
		private $_count;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	id
				FROM	support_request_items
				WHERE	id = id
			";

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
				AND		request_id = ?";
				array_push($bind_params,$request->id);
			}
			if (isset($parameters['product_id'])) {
				$find_objects_query .= "
					AND	product_id = ?";
				array_push($bind_params,$parameters['product_id']);
			}
			if (isset($parameters['serial_number'])) {
				$find_objects_query .= "
					AND	serial_number = ?";
				array_push($bind_params,$parameters['serial_number']);
			}

			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {

					$find_objects_query .= "
					AND	status IN (";
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

				if (preg_match('/^[\w\s]+$/',$parameters['status'])) {
					$find_objects_query .= "\tAND	status = ?";
					array_push($bind_params,$parameters['status']);
				}
			}

			$find_objects_query .= "
				ORDER BY id DESC
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

		public function count() {
			return $this->_count;
		}

		public function error() {
			return $this->_error;
		}
	}
?>