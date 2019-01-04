<?php
	namespace Network;

	class HostList {
		private $_error;
		private $_count;

		public function find($parameters) {
			$get_list_query = "
				SELECT	id
				FROM	network_hosts
				WHERE	id = id
			";

			$bind_params = array();

			if (isset($parameters['domain_id']) && $parameters['domain_id'] > 0) {
				$get_list_query .= "
				AND	domain_id = ?";
				array_push($bind_params,$parameters['domain_id']);
			}
			if (isset($parameters['name']) && strlen($parameters['name'])) {
				$get_list_query .= "
				AND		name = ?";
				array_push($bind_params,$parameters['name']);
			}
			if (isset($parameters['os_name']) && strlen($parameters['os_name'])) {
				$get_list_query .= "
				AND		os_name = ?";
				array_push($bind_params,$parameters['os_name']);
			}
			if (isset($parameters['os_version']) && strlen($parameters['os_version'])) {
				$get_list_query .= "
				AND		os_version = ?";
				array_push($bind_params,$parameters['os_version']);
			}

			$get_list_query .= "
				ORDER BY name";

			$rs = $GLOBALS['_database']->Execute(
				$get_list_query,
				$bind_params
			);

			if (! $rs) {
				$this->_error = "SQL Error in Network::HostList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}

			$objects = array();
			while(list($id) = $rs->FetchRow()) {
				$object = new Host($id);
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