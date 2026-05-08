<?php
	namespace Site;
	
	class Configuration Extends \BaseModel {

		/** @var array<string, array{result: bool, key: mixed, value: mixed, readOnly: bool}> Per-request memo for Configuration::get */
		private static $_requestGetCache = array();

		public $key;
		public $value;
		public bool $readOnly = false;
		private string $_sensitivePattern = '/(slack|password|token|private_key|secret|key|username|hostname|captcha_bypass|database)/i';
		private string $_uselessPattern = '/^(ADODB|_ADODB|ENV|MODE|PATH|PHP|DB_AUTOQUERY)/';

		protected $_fields = array('key','value');

		public function __construct($key = null) {
			$this->_tableName = 'site_configurations';
			$this->_tableUKColumn = 'key';
			if (isset($key)) {
				$this->key = $key;
				$this->get($key);
			}
    		parent::__construct();			
		}

		public function set($value='') {

			$this->clearError();
			$database = new \Database\Service();

			$set_config_query = "
				INSERT
				INTO	site_configurations
				(	`key`,`value`)
				VALUES 	(?,?)
				ON DUPLICATE KEY UPDATE
					`value` = ?
			";
			$database->AddParam($this->key);
			$database->AddParam($value);
			$database->AddParam($value);
			$database->Execute($set_config_query);
			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			else {
				$this->value = $value;
				app_log("Set ".$this->key." to $value");
				unset(self::$_requestGetCache[$this->key]);
				$this->purgeSharedConfigurationCache($this->key);
				return true;
			}
		}

		private function configurationKeyMayUseSharedCache(string $key): bool {
			return $key !== '' && ! preg_match($this->_sensitivePattern, $key);
		}

		private static function sharedConfigurationCacheKey(string $key): string {
			return 'site.configuration.snapshot.'.$key;
		}

		private function fillFromSharedConfigurationCache(string $key): bool {
			if (! $this->configurationKeyMayUseSharedCache($key)) {
				return false;
			}
			$cache = isset($GLOBALS['_CACHE_']) ? $GLOBALS['_CACHE_'] : null;
			if (! $cache || ! $cache->connected()) {
				return false;
			}
			$blob = $cache->get(self::sharedConfigurationCacheKey($key));
			if (! is_array($blob) || ! array_key_exists('result', $blob)) {
				return false;
			}
			$this->key = $blob['key'];
			$this->value = $blob['value'];
			$this->readOnly = isset($blob['readOnly']) ? (bool) $blob['readOnly'] : false;
			self::$_requestGetCache[$key] = array(
				'result' => (bool) $blob['result'],
				'key' => $this->key,
				'value' => $this->value,
				'readOnly' => $this->readOnly,
			);
			return true;
		}

		private function pushSharedConfigurationCache(string $key, bool $result): void {
			if (! $this->configurationKeyMayUseSharedCache($key)) {
				return;
			}
			$cache = isset($GLOBALS['_CACHE_']) ? $GLOBALS['_CACHE_'] : null;
			if (! $cache || ! $cache->connected()) {
				return;
			}
			$expires = isset($GLOBALS['_config']->cache->default_expire_seconds)
				? (int) $GLOBALS['_config']->cache->default_expire_seconds
				: 3600;
			$blob = array(
				'result' => $result,
				'key' => $this->key,
				'value' => $this->value,
				'readOnly' => $this->readOnly,
			);
			$cache->set(self::sharedConfigurationCacheKey($key), $blob, $expires);
		}

		private function purgeSharedConfigurationCache(string $key): void {
			if (! $this->configurationKeyMayUseSharedCache($key)) {
				return;
			}
			$cache = isset($GLOBALS['_CACHE_']) ? $GLOBALS['_CACHE_'] : null;
			if (! $cache || ! $cache->connected()) {
				return;
			}
			$cache->delete(self::sharedConfigurationCacheKey($key));
		}

		private function rememberGet(string $key, bool $result): bool {
			self::$_requestGetCache[$key] = array(
				'result' => $result,
				'key' => $this->key,
				'value' => $this->value,
				'readOnly' => $this->readOnly,
			);
			$this->pushSharedConfigurationCache($key, $result);
			return $result;
		}
		
		public function get($key): bool {
			if (isset(self::$_requestGetCache[$key])) {
				$snap = self::$_requestGetCache[$key];
				$this->key = $snap['key'];
				$this->value = $snap['value'];
				$this->readOnly = $snap['readOnly'];
				return $snap['result'];
			}

			if ($this->fillFromSharedConfigurationCache($key)) {
				return self::$_requestGetCache[$key]['result'];
			}

			$this->clearError();
			$this->readOnly = false;
			$database = new \Database\Service();

			$get_config_query = "
				SELECT	`key`,`value`
				FROM	site_configurations
				WHERE	`key` = ?
			";
			$database->AddParam($key);
			$rs = $database->Execute($get_config_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}
			list($this->key,$this->value) = $rs->FetchRow();

			if (empty($this->key)) {
				app_log("No Record in DB for $key, Checking Config Global",'trace');
				if (isset($GLOBALS['_config']->site->{$key})) {
					$this->key = $key;
					$this->value = $GLOBALS['_config']->site->{$key};
					$this->readOnly = true;
					return $this->rememberGet($key, true);
				}
				// Also check register config section
				elseif (isset($GLOBALS['_config']->register->{$key})) {
					$this->key = $key;
					$this->value = $GLOBALS['_config']->register->{$key};
					$this->readOnly = true;
					return $this->rememberGet($key, true);
				}
				else {
					$this->key = $key;
					$this->value = null;
					$this->readOnly = false;
					return $this->rememberGet($key, false);
				}
			}
			else {
				app_log("Config record ".$this->key." found with ".$this->value);
				$this->readOnly = false;
				return $this->rememberGet($key, true);
			}
		}

		public function getValue($key) {
			if ($this->get($key)) {
				return $this->value;
			}
			else {
				return null;
			}
		}

		/** @method public getValueBool(key)
		 * Get configuration value as boolean
		 * Returns True for '1', 'true', 'on', 'yes' (case insensitive), False otherwise
		 * Be careful not to let false outcome provide additional privileges
		 * @param string $key Configuration key to retrieve
		 * @return bool Boolean value of the configuration
		*/
		public function getValueBool($key): bool {
			if ($this->get($key)) {
				return filter_var($this->value, FILTER_VALIDATE_BOOLEAN);
			}
			else {
				return false;
			}
		}

        /**
         * add by params
         * 
         * @param array $parameters, name value pairs to add and populate new object by
         */
		public function add($parameters = []) {

			$database = new \Database\Service();
	
    		$addQuery = "INSERT INTO `$this->_tableName` ";
			$bindFields = array();
	        foreach ($parameters as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->_fields())) {
    	            array_push($bindFields, $fieldKey);
					$database->AddParam($fieldValue);
	            }
	        }
	        $addQuery .= '(`'.implode('`,`',$bindFields).'`';
            $addQuery .= ") VALUES (" . trim ( str_repeat("?,", count($bindFields)) ,',') . ")";

            // Execute DB Query
            $database->Execute($addQuery);
			if ($database->ErrorMsg()) {
				$this->_error .= $database->ErrorMsg();
				return false;
			}
			
			// get recent added row id to return update() and details()
			$this->id = $database->Insert_ID();	
			
            // audit the add event
            $auditLog = new \Site\AuditLog\Event();
            $auditLog->add(array(
                'instance_id' => $this->id,
                'description' => 'Added new '.$this->_objectName(),
                'class_name' => get_class($this),
                'class_method' => 'add'
            ));

			return true;
		}

        /**
         * update by params
         * 
         * @param array $parameters, name value pairs to update object by
         */
        public function update($parameters = []): bool {
			
			$this->clearError();
			$database = new \Database\Service();
            $updateQuery = "UPDATE `$this->_tableName` SET `key` = '$this->key'";
	        foreach ($parameters as $fieldKey => $fieldValue) {
	            if (in_array($fieldKey, $this->_fields)) {
	               $updateQuery .= ", `$fieldKey` = ?";
	               $database->AddParam($fieldValue);
	            }
	        }
	        
            $updateQuery .= " WHERE `key` = '$this->key'";
            $database->Execute($updateQuery);

			if ($database->ErrorMsg()) {
				$this->SQLError($database->ErrorMsg());
				return false;
			}

			unset(self::$_requestGetCache[$this->key]);
			$this->purgeSharedConfigurationCache($this->key);

			// audit the update event
			$auditLog = new \Site\AuditLog\Event();
			$auditLog->add(array(
				'instance_id' => $this->id,
				'description' => 'Updated '.$this->_objectName(),
				'class_name' => get_class($this),
				'class_method' => 'update'
			));
			
            // Clear Cache to Allow Update
			$cache = $this->cache();
			if (isset($cache)) $cache->delete();

            return true;
		}

		/**
		 * Determine if configuration is sensitive based on pattern match
		 * @return bool True if sensitive, False if not
		 */
		public function isSensitive(): bool {
			if (preg_match($this->_sensitivePattern, $this->key)) {
				return true;
			}
			else {
				return false;
			}
		}

		/** @method public isUseful()
		 * Determine if configuration is useful based on pattern match
		 * @return bool True if useful, False if not
		 */
		public function isUseful(): bool {
			if (preg_match($this->_uselessPattern, $this->key)) {
				return false;
			}
			else {
				return true;
			}
		}

        public function getByKey($key) {
            $this->key = $key;
            $this->get($key);
            return $this->value;
        }

		public function value() {
			return $this->value;
		}

		public function key() {
			return $this->key;
		}

		public function validKey($string) {
			if (preg_match('/^[\/\w\.\-\_\s]*$/',$string)) return true;
			else return false;
		}

		public function validValue($string) {
			return true;
		}
	}
