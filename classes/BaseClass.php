<?php
	class BaseClass {
		private $_error;

		public function error($value = null) {
			if (isset($value)) $this->_error = $value;
			return $this->_error;
		}
	}
