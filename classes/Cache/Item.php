<?php
	namespace Cache;
	
	class Item Extends \BaseClass {
		private $_client;
		private $_key;

		/** @constructor
		 * Create a new cache item
		 * @param \Cache\Client $client The cache client to use
		 * @param string $key The key for this cache item
		 */
		public function __construct($client,$key) {
			$this->_client = $client;
			$this->_key = $key;
			app_log("Creating cache item with key: ".$this->_key,'trace');
			if (! $this->_client->connected()) {
				$this->error("Client not connected");
			} elseif (! $this->_key) {
				$this->error("Key required");
			}
		}

		/** @method set()
		 * Set the value of this cache item
		 * @param mixed $value The value to store in the cache
		 * @return bool True if set, false if not
		 */
		public function set($value) {
			if (! $this->_key) {
				$this->error("Key required");
				return null;
			}
			
			if ($this->_client->set($this->_key,$value)) {
				return true;
			} else {
				$this->error($this->_client->error());
				return false;
			}
		}

		/** @method get()
		 * Get the value of this cache item
		 * @return mixed The value stored in the cache, or null if not found
		 */
		public function get() {
			return $this->_client->get($this->_key);
		}

		/** @method exists()
		 * Check if this cache item exists
		 * @return bool True if exists, false if not
		 */
		public function exists($nothing = null) {
			$object = $this->_client->get($this->_key);
			if (! empty($object)) return true;
			else return false;
		}

		/** @method key()
		 * return the key for this cache item
		 * @return string
		 */
		public function key() {
			return $this->_key;
		}

		/** @method delete()
		 * Delete this cache item
		 * @return bool True if deleted, false if not
		 */
		public function delete() {
			app_log("Deleting cache of ".$this->_key,'trace');
			if (empty($this->_client)) return true;
			if (empty($this->_key)) return true;
			if (!$this->_client->exists($this->_key)) {
				return true; // Nothing to delete
			}
			if ($this->_client->delete($this->_key)) return true;
			else return false;
		}
	}
