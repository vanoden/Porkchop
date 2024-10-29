<?php
    namespace Site;
	class CounterList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\Counter';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			$names = $GLOBALS['_CACHE_']->counters();

			$objects = array();
			foreach ($names as $name) {
				$object = new \Site\Counter($name);
				array_push($objects,$object);
				$this->incrementCount();
			}
			return $objects;
		}
	}
