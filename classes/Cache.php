<?
	class Cache {
		public $mechanism;
		public $error;
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
			$this->mechanism->set($value);
			if ($this->mechanism->error) {
				$this->error = $this->mechanism->error;
				return false;
			}
			else {
				return true;
			}
		}
		public function get() {
			$value = $this->mechanism->get();
			if ($this->mechanism->error) {
				$this->error = $this->mechanism->error;
				return null;
			}
			else {
				return value;
			}
		}
		public function delete() {
			$this->mechanism->delete();
			if ($this->mechanism->error) {
				$this->error = $this->mechanism->error;
				return false;
			}
			else {
				return true;
			}
		}
	}
?>
