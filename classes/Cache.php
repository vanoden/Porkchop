<?
	class Cache {
		public $mechanism;
		public function __construct($key) {
			$mechanism = "";
			if (isset($GLOBALS['_config']->cache) && isset($GLOBALS['_config']->cache->mechanism)) $mechanism = $GLOBALS['_config']->cache->mechanism;
			$this->key = $key;
			if ($mechanism == 'memcache') {
				$this->mechanism = new \Cache\MemCache($key);
			}
			elseif($mechanism == 'file') {
				$this->mechanism = new \Cache\File($key);
			}
			elseif($mechanism == 'xcache') {
				$this->mechanism = new \Cache\XCache($key);
			}
			else {
				$this->mechanism = new \Cache\NoCache($key);
			}
		}
		public function set($value) {
			return $this->mechanism->set($value);
		}
		public function get() {
			return $this->mechanism->get();
		}
		public function delete() {
			return $this->mechanism->delete();
		}
	}
?>
