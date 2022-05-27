<?php
	namespace Cache\Client;

	class AWSMemcache Extends Memcache {
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

			$this->_service = new \Memcached($GLOBALS['_config']->cache->persistent_id);
			$this->_service->setOption(\Memcached::OPT_CLIENT_MODE, \Memcached::DYNAMIC_CLIENT_MODE);
			if (! $this->_service->addServer($this->_host,$this->_port)) {
				$this->error = "Cannot connect to cache service";
				$this->_connected = false;
			}
			else {
				$this->_connected = true;
			}
		}
	}
