<?php
	namespace Network;

	class AddressList {
		private $_error;
		private $_count;

		public function find($parameters) {
			$get_list_query = "
				SELECT	id
				FROM	network_addresses
				WHERE	id = id
			";

			$bind_params = array();

			if (isset($parameters['adapter_id']) && $parameters['adapter_id'] > 0) {
				$get_list_query .= "
				AND	adapter_id = ?";
				array_push($bind_params,$parameters['adapter_id']);
			}
			if (isset($parameters['type']) && strlen($parameters['type']) > 0) {
				$get_list_query .= "
				AND		type = ?";
				array_push($bind_params,$parameters['type']);
			}

			$get_list_query .= "
				ORDER BY adapter_id,type";

			$rs = $GLOBALS['_database']->Execute(
				$get_list_query,
				$bind_params
			);

			if (! $rs) {
				$this->_error = "SQL Error in Network::IPAddressList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new IPAddress($id);
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
?>