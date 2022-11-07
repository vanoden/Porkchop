<?php
	namespace Site;

	class CounterWatched extends \ORM\BaseModel {

        public $id;	
        public $key;
        public $notes;
        public $tableName = 'counters_watched';
        public $fields = array('key','notes');

		public function deleteByKey($keyName) {
			$deleteObjectQuery = "DELETE FROM `$this->tableName` WHERE `key` = ?";
			$this->execute($deleteObjectQuery,array($keyName));
			if ($this->_error) return false;
			return true;
		}

		public function validKey($key) {
			if (preg_match('/^\w[\w\-\.\_]+$/',$key)) return true;
			else return false;
		}
	}