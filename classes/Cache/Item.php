<?php
	namespace Cache;
	
	class Item {
		private $_error = '';
		private $_client;
		private $_key;

		public function __construct($client,$key) {
			$this->_client = $client;
			$this->_key = $key;
			if (! $this->_client->connected()) $this->error("Client not connected");
			elseif (! $this->_key) $this->error("Key required");
		}

		public function set($value) {
			if (! $this->_key) {
				$this->error("Key required");
				return null;
			}
			
			if ($this->_client->set($this->_key,$value)) {
				return true;
			} else {
				$this->error($this->_client->error());
				return false;
			}
		}

		public function get() {
			return $this->_client->get($this->_key);
		}

		public function exists($nothing = null) {
			$object = $this->_client->get($this->_key);
			if (! empty($object)) return true;
			else return false;
		}

		public function key() {
			return $this->_key;
		}

		public function delete() {
			app_log("Deleting cache of ".$this->_key);
			if ($this->_client->delete($this->_key)) return true;
			else return false;
		}

		public function error(string $string = null) {
			if (isset($string)) $this->_error = $string;
			return $this->_error;
		}
	}
