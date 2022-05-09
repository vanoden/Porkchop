<?php
	namespace Site;

	class Counter Extends \BaseClass {
		private $_key;

		public function __construct($key) {
			if (!$this->_valid($key)) {
				$this->error("Invalid code for counter");
			}
			else {
				$this->_key = $key;
				$this->get();
			}
		}

		public function code($value = null) {
			if (isset($value) && $this->_valid($value)) {
				$this->_key = $value;
			}
			elseif (isset($value)) {
				$this->error("Invalid code");
				return null;
			}
			return $this->_key;
		}

		public function get() {
			return $GLOBALS['_CACHE_']->get("counter.".$this->_key);
		}

		public function set($value) {
			return $GLOBALS['_CACHE_']->set("counter.".$this->_key,$value);
		}

		public function increment() {
			return $GLOBALS['_CACHE_']->increment("counter.".$this->_key);
		}

		private function _valid($key) {
			if (preg_match('/^\w[\w\-\.\_]+$/',$key)) return true;
			else return false;
		}
	}
