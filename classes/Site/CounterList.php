<?php
    namespace Site;
	class CounterList Extends \BaseClass {
		public function find($parameters = array('showCacheObjects' = true)) {
			$keys = $GLOBALS['_CACHE_']->keys();
			$filteredKeys = array();
			if ($parameters['showCacheObjects'] == false) {
			    foreach ($keys as $key) if (!preg_match('/\[[0-9]+\]/', $key)) $filteredKeys[] = $key;			        
			} else {
    			$filteredKeys = $keys;
			}
			return $filteredKeys;	
		}
	}
