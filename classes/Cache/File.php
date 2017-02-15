<?
	namespace Cache;
	
	class File {
		public $key;

		public function __construct($key) {
			$this->key = $key;
		}
		public function set($value,$expires=0) {
			if (is_dir($GLOBALS['_config']->cache->path) || mkdir($GLOBALS['_config']->cache->path,0700,true)) {
				if ($fh = fopen($GLOBALS['_config']->cache->path."/".$this->key,'w')) {
					$value = serialize($value);
					fwrite($fh,$value);
					fclose($fh);
				}
				else {
					app_log("Cannot create cache file",'error',__FILE__,__LINE__);
				}
			}
			else {
				app_log("Cannot create cache path",'error',__FILE__,__LINE__);
			}
		}
		public function delete() {
			if (is_dir($GLOBALS['_config']->cache->path)) {
				$filename = $GLOBALS['_config']->cache->path."/".$this->key;
				unlink($filename);
			}
		}
		public function get() {
			if (is_dir($GLOBALS['_config']->cache->path)) {
				$filename = $GLOBALS['_config']->cache->path."/".$this->key;
				if (! file_exists($filename)) {
					app_log("No cache available",'debug',__FILE__,__LINE__);
					return null;
				}
				if ($fh = fopen($filename,'r')) {
					$content = fread($fh,filesize($filename));
					$value = unserialize($content);
					fclose($fh);
					return $value;
				}
				else {
					app_log("Cannot open cache file '$filename'",'error',__FILE__,__LINE__);
				}
			}
			else {
				app_log("Cannot access cache path",'error',__FILE__,__LINE__);
			}
		}
	}
?>