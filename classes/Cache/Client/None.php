<?php
	namespace Cache\Client;

	class None {
		private $_path;
		private $_connected;
		public $error;

		public function __construct($properties) {
		}

		public function mechanism () {
			return 'None';
		}
		public function connect() {
			$this->_connected = 1;
			return true;
		}

		public function connected() {
			return true;
		}

		public function set($key,$value,$expires=0) {
			return true;
		}

		public function delete($key) {
			return true;
		}

		public function get($key) {
			return null;
		}
	}
?>
