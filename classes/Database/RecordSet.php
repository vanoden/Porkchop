<?php
	namespace Database;

	class RecordSet Extends \BaseModel {
	
		private $_handle;
		public function __construct($handle) {
			$this->_handle= $handle;
		}

		public function FetchRow() {
			if (!isset($this->_handle) || gettype($this->_handle) != 'object') return null;
			return $this->_handle->FetchRow();
		}

		public function FetchNextObject($option_1 = false) {
			if (!isset($this->_handle) || empty($this->_handle)) return null;
			return $this->_handle->FetchNextObject($option_1);
		}

		/**
		 * Get the number of rows in the result set
		 * @return int 
		 */
		public function Rows(): int {
			if (!isset($this->_handle)) return 0;
			return $this->_handle->numRows();
		}

		public function RecordCount(): int {
			if (!isset($this->_handle)) return 0;
			if (!isset($this->_handle->numRows)) return 0;
			return $this->_handle->numRows();
		}

		public function Fields() {
			if (!isset($this->_handle)) return 0;
			return $this->_handle->fields;
		}
	}
