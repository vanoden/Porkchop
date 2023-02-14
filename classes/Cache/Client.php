<?php
	namespace Cache;

	class Client Extends \BaseModel {
		public static function connect($mechanism,$properties = array()) {
			if (preg_match('/^files?$/i',$mechanism)) return new \Cache\Client\File($properties);
			elseif (preg_match('/^aws\-memcached?$/i',$mechanism)) return new \Cache\Client\AWSMemcache($properties);
			elseif (preg_match('/^memcached?$/i',$mechanism)) return new \Cache\Client\Memcache($properties);
			else return new \Cache\Client\None($properties);
		}
	}
