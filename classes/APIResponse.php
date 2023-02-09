<?php
	class APIResponse Extends \HTTP\Response {
		protected $_data = array();

		public function success(bool $value): bool {
			if ($value) $this->success = 1;
			else $this->success = 0;
			if ($this->success = 1) return true;
			else return false;
		}

		public function data(array $data) {
			$this->_data = $data;
		}
	}
?>