<?php
	namespace Cache\Client;

	class Memcache {
		private $_host = '127.0.0.1';
		private $_port = 11211;
		private $_connected = false;
		public $error;
		private $_service;

		public function __construct($properties = null) {
			if (is_object($properties)) {
				if (isset($properties->host) && preg_match('/^\w[\w\.\-]+$/',$properties->host)) $this->_host = $properties->host;
				if (isset($properties->port) && is_numeric($properties->port)) $this->_port = $properties->port;
			}

			$this->_service = new \Memcached();
			if (! $this->_service->addServer($this->_host,$this->_port)) {
				$this->error = "Cannot connect to cache service";
				$this->_connected = false;
			}
			else {
				$this->_connected = true;
			}
		}

		public function flush() {
			return $this->_service->flush();
		}
		public function stats() {
			return $this->_service->getStats();
		}

		public function mechanism () {
			return 'Memcache';
		}

		public function connected() {
			if ($this->_connected) return true;
			return false;
		}

		public function set($key,$value,$expires=0) {
			if ($this->_connected) {
				if ($this->_service->set($key,$value,$expires)) return true;
				else $this->error = "Error storing cache value for '$key': ".$this->_service->getResultCode();
			}
			else {
				$this->error = "Cache client not connected";
				return false;
			}
		}

		public function delete($key) {
			if ($this->_connected) {
				if ($this->_service->delete($key)) return true;
				else {
					$this->error = "Unable to delete value from cache";
					return false;
				}
			}
			else {
				$this->error = "Cache client not connected";
				return false;
			}
		}

		public function get($key) {
			if ($this->_connected) {
				$value = $this->_service->get($key);
				if (isset($value)) return $value;
				else return null;
			}
			else {
				$this->error = "Cache client not connected";
			}
		}

		public function keys($object = null) {
			$keyArray = array();
			$keys = $this->_service->getAllKeys();
			foreach ($keys as $key) {
				preg_match('/^(\w[\w\-\.\_]*)\[(\d+)\]$/',$key,$matches);
				if (is_null($object) || $object == $matches[1]) {
					$key = sprintf("%s[%d]",$matches[1],$matches[2]);
					array_push($keyArray,$key);
				}
			}
			return $keyArray;
		}
	}
?>
