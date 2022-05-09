<?php
	class CounterList Extends \BaseClass {
		public function find() {
print_r("Finding keys",false);
			$keys = $GLOBALS['_CACHE_']->keys();
			return $keys;
		}
	}
