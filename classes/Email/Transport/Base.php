<?php
	namespace Email\Transport;

	class Base {
		private $_error;

		public function result() {
			return $this->_result;
		}

		public function error() {
			return $this->_error;
		}
	}
?>