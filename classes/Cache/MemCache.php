<?
	namespace Cache;
	
	class MemCache {
		public $key;
		private $service;
		public $error;
		public function __construct($key) {
			if (! isset($GLOBALS['_memcache'])) {
				$this->error = "Memcache not initiated";
				return null;
			};
			$this->service = $GLOBALS['_memcache'];
			$this->key = $key;
		}

		public function set($value,$expires=0) {
			$result = $this->service->set($key,$value,0,$expires);
			if (! $result) {
				app_log("Error setting cache: ".$this->service->getResultCode(),'error',__FILE__,__LINE__);
				$this->error = "Errot setting cache";
				return null;
			}
			return $result;
		}

		public function delete() {
			return $this->service->delete($this->key);
		}

		public function get() {
			try {
				$value = $this->service->get($this->key);
			}
			catch (Exception $e) {
				app_log(print_r(debug_backtrace(),true),'trace',__FILE__,__LINE__);
				$this->error = "Error getting cache: $e->getMessage()";
				return null;
			}
			return $value;
		}
	}
?>
