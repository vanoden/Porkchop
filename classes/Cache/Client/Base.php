<?php
	namespace Cache\Client;

	class Base {
		private string $_error = '';

		public function error(string $value = null): string {
			if (isset($value)) $this->_error = $value;
			return $this->_error;
		}
	}