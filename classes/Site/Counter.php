<?php
	namespace Site;

	class Counter Extends \BaseClass {
		private $_key;
		public $value;

		public function __construct($key) {
			if (!$this->validKey($key)) {
				$this->error("Invalid code for counter");
			}
			else {
    			$this->_key = $key;

			    $this->value = $this->get();   
			}
		}

		public function code($value = null) {
			if (isset($value) && $this->validKey($value)) {
				$this->_key = $value;
			} elseif (isset($value)) {
				$this->error("Invalid code");
				return null;
			}
			return $this->_key;
		}

        public function setWatched(array $keysList) {
            return $GLOBALS['_CACHE_']->set("watched.counters",$keysList);
        }
        
        public function getWatched() {
            return $GLOBALS['_CACHE_']->get("watched.counters");
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

		public function value() {
			return $this->value;
		}

		public function validKey($key) {
			if (preg_match('/^\w[\w\-\.\_]+$/',$key)) return true;
			else return false;
		}
	}
