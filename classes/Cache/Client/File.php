<?php
	namespace Cache\Client;

	class File Extends Base {
		private $_path;
		private $_connected;

		public function __construct($properties) {
			// prefix is optional; if unset or empty, no prefix is used (backward compatible)
			$this->_prefix = (isset($properties->prefix) && $properties->prefix !== '')
				? preg_replace('/[^a-zA-Z0-9_-]/', '_', $properties->prefix) : '';
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
				$this->incrementStat("total_connections");
				return true;
			}
		}

		public function connected() {
			if ($this->_connected) return true;
			return false;
		}

		/** @method set()
		 * Set a value in the cache for the given key
		 * @param string $key The key to store the value under
		 * @param mixed $value The value to store in the cache
		 * @param int $expires The expiration time in seconds (default 0 for no expiration) 
		 * @return bool True if set, false if not
		 */
		public function set($key,$value,$expires=0) {
			if (!$this->_connected && ! $this->connect()) {
				return false;
			}
			else {
				$internalKey = $this->prefixKey($key);
				if (! preg_match('/^_/',$key)) $this->incrementStat("cmd_set");
				$path = $this->_path;
				if (!is_dir($path)) {
					$this->_error = "Cache Directory Doesn't Exists";
					return false;
				}
				elseif (!is_writable($path)) {
					$this->_error = "Cache Directory Not Writable";
					return false;
				}
				elseif ($fh = fopen($path."/".$internalKey,'w')) {
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

		/** @method delete()
		 * Delete a value from the cache for the given key
		 * @param string $key The key to delete from the cache
		 * @return bool True if deleted, false if not
		 */
		public function delete($key) {
			app_log("Deleting cache of ".$key,'trace');
			if ($this->_connected) {
				$internalKey = $this->prefixKey($key);
				$filename = $GLOBALS['_config']->cache->path."/".$internalKey;
				if (file_exists($filename)) {
					app_log("Cache file exists: ".$filename,'trace');
					if (unlink($filename)) {
						if (! preg_match('/^_/',$key)) $this->incrementStat("delete_hits");
					}
					else {
						$this->_error = "Unable to unset cache";
						return false;
					}
				}
			}
			return true;
		}

		public function get($key) {
			if ($this->_connected) {
				$internalKey = $this->prefixKey($key);
				if (! preg_match('/^_/',$key)) $this->incrementStat("cmd_get");
				$filename = $this->_path."/".$internalKey;
				if (! file_exists($filename)) {
					if (! preg_match('/^_/',$key)) $this->incrementStat("get_misses");
					return null;
				}
				if (filesize($filename) > 0 && $fh = fopen($filename,'r')) {
					$content = fread($fh,filesize($filename));
					$value = unserialize($content);
					fclose($fh);
					if (! preg_match('/^_/',$key)) $this->incrementStat("get_hits");
					return $value;
				}
				elseif (filesize($filename) == 0) {
					unlink ($filename);
				}
				else {
					$this->error("Cannot open cache file '$filename'");
				}
			}
			else {
				$this->error("Cache client not connected");
			}
		}

		public function increment($key) {
			$current = $this->get($key);
			if ($this->error()) return null;
			if (! isset($current)) {
				if (! preg_match('/^_/',$key)) $this->incrementStat("incr_misses");
				$current = 0;
			}
			elseif (! preg_match('/^_/',$key)) $this->incrementStat("incr_hits");
			elseif (!is_bool($current)) $current ++;
			if ($this->set($key,$current)) {
				return $this->get($key);
			}
            else {
                return false;
            }
		}

		public function keys($object = null) {
			$keyArray = array();
			if ($this->_connected) {
				$keys = scandir($GLOBALS['_config']->cache->path."/");
				foreach ($keys as $key) {
					if (!$this->keyHasOurPrefix($key)) continue;
					$key = $this->unprefixKey($key);
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

		public function counters() {
			$keyArray = array();
			$keys = scandir($GLOBALS['_config']->cache->path."/");
			foreach ($keys as $key) {
				if (!$this->keyHasOurPrefix($key)) continue;
				$key = $this->unprefixKey($key);
				if (! preg_match('/^counter\.(\w[\w\.\_\-]*)/',$key,$matches)) continue;
				array_push($keyArray,$matches[1]);
			}
			return $keyArray;
		}

		public function keyNames() {
			$keyNames = array();
			if ($this->_connected) {
				$keys = scandir($GLOBALS['_config']->cache->path."/");
				foreach ($keys as $key) {
					if (!$this->keyHasOurPrefix($key)) continue;
					$key = $this->unprefixKey($key);
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
					if (!$this->keyHasOurPrefix($key)) continue;
					if (preg_match('/^[\w\-\.\_]+\[\d+\]$/',$this->unprefixKey($key))) {
						unlink($GLOBALS['_config']->cache->path."/".$key);
					}
				}
			}
		}

		public function curr_items() {
			return count($this->keys());
		}

		public function stats() {
			if (!$this->_connected && ! $this->connect()) {
				return false;
			}
			$system = new \System();

			$this->incrementStat("cmd_stats");

			$stats = array();
			$stats["type"] = $this->mechanism();
			$stats["uptime"] = $system->uptime();
			$stats["version"] = $system->version();
			$stats["curr_items"] = $this->curr_items();
			$stats["cmd_get"] = $this->getStat("cmd_get");
			$stats["cmd_set"] = $this->getStat("cmd_set");
			$stats["cmd_delete"] = $this->getStat("cmd_delete");
			$stats["cmd_stats"] = $this->getStat("cmd_stats");
			$stats["get_hits"] = $this->getStat("get_hits");
			$stats["get_misses"] = $this->getStat("get_misses");
			$stats["incr_hits"] = $this->getStat("incr_hits");
			$stats["incr_misses"] = $this->getStat("incr_misses");
			$stats["total_connections"] = $this->getStat("total_connections");

			return $stats;
		}

		public function incrementStat($key) {
			$this->increment("_".$key);
		}

		public function getStat($key) {
			return $this->get("_".$key);
		}

		public function exists($key) {
			if ($this->_connected) {
				return file_exists($this->_path."/".$this->prefixKey($key));
			}
			return false;
		}
	}
