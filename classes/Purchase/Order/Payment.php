<?php
	namespace Purchase\Order;

	class Payment {
		public $id;
		private $_error;

		public function __construct($id = null) {
			if (!empty($id)) {
				$this->id = $id;
				return $this->details();
			}
		}

		public function add($params = array()) {
			return $this->update($params);
		}

		public function update($params = array()) {
			return $this->details();
		}

		public function error($error = null) {
			if (!empty($error)) $this->_error = $error;
			return $this->_error;
		}
	}
