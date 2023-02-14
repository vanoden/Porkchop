<?php
	namespace Email\Transport;

	class Base Extends \BaseModel {
		public function hostname() {
			return $this->hostname;
		}

		public function token() {
			return $this->token;
		}

		public function result() {
			return $this->_result;
		}
	}
