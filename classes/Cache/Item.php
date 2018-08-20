<?php
	namespace Cache;
	
	class Item {
		private $_client;
		private $_key;
		public $error;

		public function __construct($client,$key) {
			$this->_client = $client;
			$this->_key = $key;
		}

		public function set($value) {
			return $this->_client->set($this->_key,$value);
		}

		public function get() {
			return $this->_client->get($this->_key);
		}

		public function key() {
			return $this->_key;
		}

		public function delete() {
			if ($this->_client->delete($this->_key)) return true;
			else return false;
		}
	}
?>