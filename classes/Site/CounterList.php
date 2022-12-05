<?php
    namespace Site;
	class CounterList Extends \BaseClass {
		public function find($parameters = array('showCacheObjects' => true)) {
			$this->clearErrors();
			$this->resetCount();
			$names = $GLOBALS['_CACHE_']->counters();
			$counters = array();
			foreach ($names as $name) {
				$counter = new \Site\Counter("counter.".$name);
				array_push($counters,$counter);
				$this->incrementCount();
			}
		}
	}
