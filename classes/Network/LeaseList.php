<?php
	namespace Network;

	class LeaseList {
		private $_error;

		public function find($parameters = array()) {
			$find_objects_query = "
				SELECT	mac_address
				FROM	network_leases
				WHERE	mac_address = mac_address
			";

			if (isset($parameters["mac_address"])) {
				if (preg_match('/^[a-f\d\:]{17}$/',$parameters['mac_address'])) {
					$find_objects_query .= "
						AND		mac_address = ".$GLOBALS['_database']->qstr($parameters['mac_address'],get_magic_quotes_gpc);
				}
				else {
					$this->_error = "Invalid mac address";
					return null;
				}
			}
			if (isset($parameters["ip_address"])) {
				if (preg_match('/^\d+\.\d+\.\d+\.\d+$/',$parameters['ip_address'])) {
					$find_objects_query .= "
						AND		ip_address = ".$GLOBALS['_database']->qstr($parameters['ip_address'],get_magic_quotes_gpc);
				}
				else {
					$this->_error = "Invalid ip address";
					return null;
				}
			}
			if (isset($parameters["hostname"])) {
				if (preg_match('/^[\w\.\-]+$/',$parameters['hostname'])) {
					$find_objects_query .= "
						AND		hostname = ".$GLOBALS['_database']->qstr($parameters['hostname'],get_magic_quotes_gpc);
				}
				else {
					$this->_error = "Invalid hostname";
					return null;
				}
			}

			$rs = $GLOBALS['_database']->Execute($find_objects_query);
			if (! $rs) {
				$this->_error = "SQL Error in Network::LeaseList::find(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			$objects = array();
			while(list($mac_address = $rs->FetchRow())) {
				$object = new Lease($mac_address);
				array_push($objects,$object);
			}
			return $objects;
		}
	}
