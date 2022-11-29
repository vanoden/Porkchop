<?php
	namespace Database;

	class RecordSet Extends \BaseClass {
		private $_handle;
		public function construct($handle) {
			$this->_handle= $handle;
		}

		public function FetchRow() {
			if (!isset($this->_handle)) return null;
			return $this->_handle->FetchRow();
		}

		public function FetchNextObject($option_1 = false) {
			if (!isset($this->_handle)) return null;
			return $this->_handle->FetchNextObject($option_1);
		}
	}
