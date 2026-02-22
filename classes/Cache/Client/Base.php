<?php
	namespace Cache\Client;

	class Base Extends \BaseClass {
		/** @var string Optional key prefix to isolate this site when sharing cache (file/memcache) across multiple sites */
		protected $_prefix = '';

		/**
		 * Apply configured prefix to a cache key for get/set/delete.
		 * @param string $key Raw cache key
		 * @return string Key to use with backend (prefix_key if prefix set, else key)
		 */
		protected function prefixKey($key) {
			return $this->_prefix !== '' ? $this->_prefix . '_' . $key : $key;
		}

		/**
		 * Strip prefix from an internal key when returning key names to callers.
		 * @param string $internalKey Key as stored (may include prefix_)
		 * @return string Key without prefix
		 */
		protected function unprefixKey($internalKey) {
			if ($this->_prefix !== '' && strpos($internalKey, $this->_prefix . '_') === 0) {
				return substr($internalKey, strlen($this->_prefix) + 1);
			}
			return $internalKey;
		}

		/** @return bool True if this key belongs to our prefix */
		protected function keyHasOurPrefix($internalKey) {
			return $this->_prefix === '' || strpos($internalKey, $this->_prefix . '_') === 0;
		}
	}