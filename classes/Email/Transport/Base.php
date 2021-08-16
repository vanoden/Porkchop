<?php
	namespace Email\Transport;

	class Base {
		protected $_error;

		public function hostname() {
			return $this->hostname;
		}

		public function token() {
			return $this->token;
		}

		public function result() {
			return $this->_result;
		}

		public function error() {
			return $this->_error;
		}
	}
