<?php
	class BaseListClass Extends BaseClass {
		protected $_count = 0;

        public function count() {
            return $this->_count;
        }

		public function incrementCount() {
			$this->_count ++;
		}

		public function resetCount() {
			$this->_count = 0;
		}

		public function validSearchString($string) {
			if (preg_match('/^[\w\-\.\_\s\*]*$/',$string)) return true;
			else return false;
		}
	}
