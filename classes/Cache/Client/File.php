<?php
	namespace Cache\Client;

	class File Extends \BaseClass {
		private $_path;
		private $_connected;

		public function __construct($properties) {
			if (empty($properties->path)) {
				$this->_error = 'Cache path not defined';
			}
			else if (preg_match('/^\//',$properties->path)) {
				if (is_dir($properties->path."/")) {
					if (is_writable($properties->path)) {
						$this->_path = $properties->path;
						$this->_connected = true;
					}
					else {
						$this->error("Cache path not writable");
					}
				}
				else if (file_exists($properties->path)) {
					$this->error("Cache path '".$properties->path."' does not exist");
				}
				else if (empty(filetype($properties->path))) {
					#$this->_error = "Unknown file type for '".$properties->path."'";
				}
				else {
					$this->error($properties->path."' is a ".filetype($properties->path));
				}
			}
			else {
				$this->error("Cache path not valid");
			}
		}

		public function mechanism () {
			return 'File';
		}

		public function connect() {
			if (empty($this->_path)) {
				$this->error("Cache path not configured");
				return false;
			}
			elseif (!is_dir($this->_path)) {
				$this->error("Cache path ".$this->_path." not found");
				app_log("Cache directory ".$this->_path." not found",'_error');
				return false;
			}
			elseif (!is_writable($this->_path)) {
				$this->error("Cache path not writable");
				app_log("Cache directory ".$this->_path." no writable",'_error');
				return false;
			}
			else {
				$this->_connected = 1;
				return true;
			}
		}

		public function connected() {
			if ($this->_connected) return true;
			return false;
		}

		public function set($key,$value,$expires=0) {
			if (!$this->_connected && ! $this->connect()) {
				return false;
			}
			else {
				$path = $this->_path;
				if ($fh = fopen($path."/".$key,'w')) {
					$string = serialize($value);
					fwrite($fh,$string);
					fclose($fh);
					return true;
				}
				else {
					$this->_error = "Unable to store value in cache";
					return false;
				}
			}
		}

		public function delete($key) {
			if ($this->_connected) {
				$filename = $GLOBALS['_config']->cache->path."/".$key;
				if (unlink($filename)) return true;
				else {
					$this->_error = "Unable to unset cache";
					return false;
				}
			}
		}

		public function get($key) {
			if ($this->_connected) {
				$filename = $this->_path."/".$key;
				if (! file_exists($filename)) {
					return null;
				}
				if ($fh = fopen($filename,'r')) {
					$content = fread($fh,filesize($filename));
					$value = unserialize($content);
					fclose($fh);
					return $value;
				}
				else {
					$this->_error = "Cannot open cache file '$filename'";
				}
			}
			else {
				$this->_error = "Cache client not connected";
			}
		}

		public function increment($key) {
			$current = $this->get($key);
			if ($this->_error) return null;
			if (! isset($current)) $current = 0;
			$current ++;
			if ($this->set($key,$current)) {
				return $this->get($key);
			}
		}

		public function keys($object = null) {
			$keyArray = array();
			if ($this->_connected) {
				$keys = scandir($GLOBALS['_config']->cache->path."/");
				foreach ($keys as $key) {
					if (preg_match('/(\w[\w\-\.\_]*)\[(\d+)\]$/',$key,$matches)) {
						if (is_null($object) || $object == $matches[1]) {
							$key = sprintf("%s[%d]",$matches[1],$matches[2]);
							array_push($keyArray,$key);
						}
					}
				}
			}
			return $keyArray;
		}

		public function keyNames() {
			$keyNames = array();
			if ($this->_connected) {
				$keys = scandir($GLOBALS['_config']->cache->path."/");
				foreach ($keys as $key) {
					if (preg_match('/(\w[\w\-\.\_]*)\[(\d+)\]$/',$key,$matches)) {
						$keyNames[$matches[1]] ++;
					}
				}
			}
			return $keyNames;
		}

		public function flush() {
			if ($this->_connected) {
				$keys = scandir($GLOBALS['_config']->cache->path."/");
				foreach ($keys as $key) {
					if (preg_match('/^[\w\-\.\_]+\[\d+\]$/')) {
						delete($GLOBALS['_config']->cache->path."/".$key);
					}
				}
			}
		}

		public function stats() {
			return array();
		}
	}
