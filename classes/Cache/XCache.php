<?
	namespace Cache;
	
	class XCache {
		public $key;
		public function __construct($key) {
			$this->key = $key;
		}
		public function set($value,$expires=0) {
			$value = serialize($value);
			return xcache_set($this->key,$value);
		}
		public function delete() {
			return xcache_unset($this->key);
		}
		public function function get() {
			if (xcache_isset($this->key)) {
				return unserialize(xcache_get($this->key));
			}
		}
	}
?>