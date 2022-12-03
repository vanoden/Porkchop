<?php
	namespace Database;

	class RecordSet Extends \BaseClass {
		private $_handle;
		public function __construct($handle) {
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

		public function Rows() {
			if (!isset($this->_handle)) return 0;
			return $this->_handle->numRows();
		}
	}
