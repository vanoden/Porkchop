<?php
	namespace Support\Request\Item;
	
	class RMAList {
		private $_error;
		private $_count = 0;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	sr.id
				FROM	support_rmas sr,
						support_request_items sri,
						support_requests srs
				WHERE	sr.item_id = sri.id
				AND		sri.request_id = srs.id
			";
			$bind_params = array();
			if (isset($parameters['item_id'])) {
				$item = new \Support\Request\Item($parameters['item_id']);
				if ($item->error()) {
					$this->_error = $item->error();
					return false;
				}
				if (! $item->id) {
					$this->_error = "Item not found";
					return false;
				}
				$find_objects_query .= "
				AND		sri.id = ?
				";
				array_push($bind_params,$item->id);
			}
			if (isset($parameters['organization_id']) && is_numeric($parameters['organization_id'])) {
				$organization = new \Register\Organization($parameters['organization_id']);
				if (! $organization->exists()) {
					$this->_error = "Organization not found";
					return null;
				}
				$find_objects_query .= "
					AND	srs.organization_id = ?";
				array_push($bind_params,$organization->id);
			}

			if (isset($parameters['product_id'])) {
				$product = new \Product\Item($parameters['product_id']);
				$find_objects_query .= "
					AND	sri.product_id = ?";
				array_push($bind_params,$product->id);
			}

			if (isset($parameters['status'])) {
				if (is_array($parameters['status'])) {
				}
				else {
					$find_objects_query .= "
					AND		sr.status = ?
					";
					array_push($bind_params,$parameters['status']);
				}
			}

			if (isset($parameters['date_start']) && get_mysql_date($parameters['date_start'])) {
				$find_objects_query .= "
					AND		date_approved >= ?";
				array_push($bind_params,get_mysql_date($parameters['date_start']));
			}

			if (isset($parameters['date_end']) && get_mysql_date($parameters['date_end'])) {
				$find_objects_query .= "
					AND		date_approved < ?";
				array_push($bind_params,get_mysql_date($parameters['date_end']));
			}

			query_log($find_objects_query,$bind_params);
            $rs = executeSQLByParams($find_objects_query, $bind_params);
			
			if (! $rs) {
				$this->_error = "SQL Error in Support::Request::RMAList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new \Support\Request\Item\RMA($id);
				array_push($objects,$object);
				$this->_count ++;
			}
			return $objects;
		}

		public function error() {
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
