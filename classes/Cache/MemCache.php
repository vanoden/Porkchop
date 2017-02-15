<?
	namespace Cache;
	
	class MemCached {
		public $key;
		public function __construct($key) {
			$this->key = $key;
		}

		public function set($value,$expires=0) {
			$result = $GLOBALS['_memcache']->set($key,$value,0,$expires);
			if (! $result) {
				app_log("Error setting cache: ".$GLOBALS['_config']->getResultCode(),'error',__FILE__,__LINE__);
			}
			return $result;
		}

		public function delete() {
			return $GLOBALS['_memcache']->delete($this->key);
		}

		public function get() {
			return $GLOBALS['_memcache']->get($this->key);
		}
	}
?>