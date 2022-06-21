<?php
    namespace Site;
	class CounterList Extends \BaseClass {
		public function find() {
			$keys = $GLOBALS['_CACHE_']->keys();
			return $keys;
		}
	}
