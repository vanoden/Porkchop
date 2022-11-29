<?php
	namespace Database;

	class RecordSet Extends \BaseClass {
		private $_handle;
		public function construct($handle) {
			$this->_handle= $handle;
		}

		public function FetchRow() {
			return $this->_handle->FetchRow();
		}

		public function FetchNextObject($option_1 = false) {
			return $this->_handle->FetchNextObject($option_1);
		}
	}
