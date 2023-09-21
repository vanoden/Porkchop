<?php
    namespace Site;
	class CounterList Extends \BaseListClass {
		public function find($parameters = array('showCacheObjects' => true)) {
			$this->clearError();
			$this->resetCount();
			$names = $GLOBALS['_CACHE_']->counters();
			$counters = array();
			foreach ($names as $name) {
				$counter = new \Site\Counter($name);
				array_push($counters,$counter);
				$this->incrementCount();
			}
			return $counters;
		}
	}
