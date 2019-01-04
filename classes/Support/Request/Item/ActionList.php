<?php
	namespace Support\Request\Item;
	
	class ActionList {
		private $_error;
		public $count = 0;
		
		public function find($parameters = array()) {
			$get_list_query = "
				SELECT	`id`
				FROM	`support_item_actions`
				WHERE	`id` = `id`
			";
			
            // if search term, then constrain by that
            if ($parameters['searchTerm']) {
                $get_list_query = "
				    SELECT	`id`
				    FROM	`support_item_actions`
				    WHERE `description` LIKE '%".$parameters['searchTerm']."%' 
			    ";
            }

			$bind_parameters = array();
			if (isset($parameters['item_id']) && $parameters['item_id'] > 0) {
				$item = new \Support\Request\Item($parameters['item_id']);
				if ($item->error()) {
					$this->_error = $item->error();
					return false;
				}
				if (! $item->id) {
					$this->_error = "Item not found";
					return false;
				}
				$get_list_query .= "
				AND		item_id = ?";
				array_push($bind_parameters,$item->id);
			}
			
			if (isset($parameters['assigned_id']) && is_numeric($parameters['assigned_id'])) {
				$admin = new \Register\Customer($parameters['assigned_id']);
				if ($admin->error) {
					$this->_error = $admin->error;
					return false;
				}
				if (! $admin->id) {
					$this->_error = "Admin not found";
					return false;
				}
				$get_list_query .= "
					AND	assigned_id = ?
				";
				array_push($bind_parameters,$admin->id);
			}
			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
					$get_list_query .= "
					AND	status IN (";
					$started = 0;
					foreach ($parameters['status'] as $status) {
						if (! in_array($status,array('NEW','ACTIVE','PENDING CUSTOMER','PENDING VENDOR','CANCELLED','COMPLETE'))) {
							$this->_error = "Invalid status '$status'";
							return false;
						}
						if ($started) $get_list_query .= ",";
						$get_list_query .= "'$status'";
						$started = 1;
					}
					$get_list_query .= ")";
				}
			}
			query_log($get_list_query);
			$rs = $GLOBALS['_database']->Execute($get_list_query,$bind_parameters);
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::ActionList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while (list($id) = $rs->FetchRow()) {
				$object = new \Support\Request\Item\Action($id);
				array_push($objects,$object);
				$this->count ++;
			}
			return $objects;
		}
		public function error() {
			return $this->_error;
		}
	}
