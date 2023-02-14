<?php
	namespace Site;

	class CounterWatched extends \BaseModel {
        public $key;
        public $notes;

		public function __construct($id = 0) {
			$this->_tableName = 'counters_watched';
			$this->_tableUKColumn = 'key';
			$this->_addFields(array('key','notes'));
    		parent::__construct($id);			
		}

		public function validKey($key) {
			if (preg_match('/^\w[\w\-\.\_]+$/',$key)) return true;
			else return false;
		}
	}
