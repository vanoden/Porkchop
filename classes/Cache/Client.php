<?php
	namespace Cache;

	class Client Extends \BaseClass {
		public static function connect($mechanism,$properties = array()) {
			if (preg_match('/^files?$/i',$mechanism)) return new \Cache\Client\File($properties);
			elseif (preg_match('/^memcached?$/i',$mechanism)) return new \Cache\Client\Memcache($properties);
			else return new \Cache\Client\None($properties);
		}
	}
