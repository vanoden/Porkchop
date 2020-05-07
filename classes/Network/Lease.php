<?php
	namespace Network;

	class Lease {
		private $hostname;
		private $_ip_address;
		private $_mac_address;
		private $_time_start;
		private $_time_end;
		private $_error;
		private $_present = false;

		public function __construct($mac = 0) {
			if (preg_match('/^[a-f\d\:]{17}$/',$mac)) {
				$this->_mac_address = $mac;
				$this->details();
			}
			else if($mac) {
				$this->_error = "Invalid mac address";
			else {
			}
		}

		public function get($mac_address) {
			$get_object_query = "
				SELECT	mac_address
				FROM	network_leases
				WHERE	mac_address = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_object_query,
				array($mac_address)
			);
			if ($rs->FetchRow()) {
				$this->_mac_address = $mac_address;
				return $this->details();
			}
		}

		public function ip_address() {
			return $this->_ip_address;
		}

		public function mac_address() {
			return $this->_mac_address;
		}

		public function hostname() {
			return $this->_hostname;
		}

		public function time_start() {
			return $this->_time_start;
		}

		public function time_end() {
			return $this->_time_end;
		}

		public function error() {
			return $this->_error;
		}

		public function save() {
			if (! isset($this->_hostname)) {
				$this->_error = "No hostname set";
				return 0;
			}
			if (! isset($this->_ip_address)) {
				$this->_error = "No ip address set";
				return 0;
			}
			if (! isset($this->_mac_address)) {
				$this->_error = "No mac address set";
				return 0;
			}

			$current = new Lease($this->_mac_address);
			if ($current->ip_address) {

			$leaselist = new LeaseList();
			$leases = $leaselist->find("mac_address" => $this->_mac_address);
			if(array_count($leases)) {
		}

		public function details() {
			$get_lease_query = "
				SELECT	hostname,
						ip_address,
						mac_address,
						time_start,
						time_end
				FROM	network_leases
				WHERE	mac_address = ?
			";
			$rs = $GLOBALS['_database']->Execute(
				$get_lease_query,
				array($this->_mac_address)
			);
			if (! $rs) {
				$this->error = "SQL Error in Network::Lease::details(): ".$GLOBALS['_database']->ErrorMsg();
				return null;
			}
			if ($rs->rows() > 0) $this->_present = true;
			else $this->_present = false;

			return $rs->FetchNextObject(false);
		}
	}
