<?php
	namespace Cache\Client;

	class Memcache Extends \BaseClass {
		private $_host = '127.0.0.1';
		private $_port = 11211;
		private $_connected = false;
		private $_service;

		public function __construct($properties = null) {
			if (is_object($properties)) {
				if (isset($properties->host) && preg_match('/^\w[\w\.\-]+$/',$properties->host)) $this->_host = $properties->host;
				if (isset($properties->port) && is_numeric($properties->port)) $this->_port = $properties->port;
			}

			$this->_service = new \Memcached();
			if (! $this->_service->addServer($this->_host,$this->_port)) {
				$this->error("Cannot connect to cache service");
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
			$stats = $this->_service->getStats();
			$hosts = array_keys($stats);
			$host = $hosts[0];
			$stats[$host]["type"] = $this->mechanism();
			$stats[$host]["host"] = $host;
			return $stats[$host];
		}

		public function mechanism () {
			return 'Memcache';
		}

		public function connected() {
			if ($this->_connected) return true;
			return false;
		}

		public function set($key,$value,$expires = null) {
			if (!isset($expires)) $expires = $GLOBALS['_config']->cache->default_expire_seconds;
			
			if ($this->_connected) {
				if ($this->_service->set($key,$value,$expires)) return true;
				else $this->error("Error storing cache value for '$key': ".$this->_service->getResultCode());
			}
			else {
				$this->error("Cache client not connected");
				return false;
			}
		}

		public function delete($key) {
			if ($this->_connected) {
				if ($this->_service->delete($key)) return true;
				else {
					$this->error("Unable to delete value from cache: ".$this->_service->getResultCode());
					return false;
				}
			}
			else {
				$this->error("Cache client not connected");
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
				$this->error("Cache client not connected");
			}
		}

		public function increment($key) {
			if ($this->_connected) {
                if (! $this->_service->get($key)) {
                    if ($this->set($key,1)) $this->get($key);
                    else {
                            $this->error("Error incrementing key: ".$this->_service->getResultCode());
                            return null;
                    }
                }
				if ($this->_service->increment($key)) return $this->get($key);
				else {
					$this->error("Error incrementing key: ".$this->_service->getResultCode());
					return null;
				}
			}
			else {
				$this->error("Cache client not connected");
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

		public function counters() {
			$keyArray = array();
			$keys = $this->_service->getAllKeys();
			foreach ($keys as $key) {
				if (! preg_match('/^counter\.(\w[\w\.\-\_]*)/',$key,$matches)) continue;
				array_push($keyArray,$matches[1]);
			}
			return $keyArray;
		}

		public function keyNames() {
			$keyNames = array();
			$keys = $this->_service->getAllKeys();
			foreach ($keys as $key) {
				if (preg_match('/^(\w[\w\-\.\_]*)\[(\d+)\]$/',$key,$matches)) $keyNames[$matches[1]] ++;
			}
			return $keyNames;
		}
	}
