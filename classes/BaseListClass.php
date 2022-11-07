<?php
	class BaseListClass Extends BaseClass {
		protected $_count = 0;

        public function count() {
            return $this->_count;
        }

		public function incrementCount() {
			$this->_count ++;
		}
	}
