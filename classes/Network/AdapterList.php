<?php
	namespace Network;

	class AdapterList {
		private $_error;
		private $_count;

		public function find($parameters) {
			$get_list_query = "
				SELECT	id
				FROM	network_adapters
				WHERE	id = id
			";

			$bind_params = array();

			if (isset($parameters['host_id']) && $parameters['host_id'] > 0) {
				$get_list_query .= "
				AND	host_id = ?";
				array_push($bind_params,$parameters['host_id']);
			}
			if (isset($parameters['name']) && strlen($parameters['name']) > 0) {
				$get_list_query .= "
				AND		name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['type']) && strlen($parameters['type']) > 0) {
				$get_list_query .= "
				AND		type = ?";
				array_push($bind_params,$parameters['type']);
			}
			if (isset($parameters['mac_address']) && strlen($parameters['mac_address']) > 0) {
				$get_list_query .= "
				AND		mac_address = ?";
				array_push($bind_params,$parameters['mac_address']);
			}

			$get_list_query .= "
				ORDER BY name";

			$rs = $GLOBALS['_database']->Execute(
				$get_list_query,
				$bind_params
			);

			if (! $rs) {
				$this->_error = "SQL Error in Network::AdapterList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new Adapter($id);
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
