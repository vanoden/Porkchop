<?php
	namespace Site;
		
	class ConfigurationList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\Configuration';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			// Initialize Database Service
			$database = new \Database\Service();

			// Dereference Working Class
			$workingClass = new $this->_modelName;

			// Build Query
			$find_objects_query = "
				SELECT	`key`
				FROM	site_configurations
				WHERE	`key` = `key`
			";
			
			if (!empty($parameters['key'])) {
				if ($workingClass->validCode($parameters['key'])) {
					$find_objects_query .= "
					AND `key` = ?";
					$database->AddParam($parameters['key']);
				}
				else {
					$this->error("Invalid key");
					return [];
				}
			}

			if (!empty($parameters['value'])) {
				if ($workingClass->validateValue($parameters['value'])) {
					$find_objects_query .= "
					AND `value` = ?";
					$database->AddParam($parameters['value']);
				}
				else {
					$this->error("Invalid value");
					return [];
				}
			}

			// Order Clause
			$find_objects_query .= "
					ORDER BY `key`
			";
	
			// Limit Clause
			$find_objects_query .= $this->limitClause($controls);

			// Execute Query
			$rs = $database->Execute($find_objects_query);
			if (! $rs) {
				$this->SQLError($database->ErrorMsg());
				return [];
			}

			// Build Results
			$objects = [];
			while(list($id) = $rs->FetchRow()) {
				$object = new $this->_modelName($id);
				$this->incrementCount();
				array_push($objects,$object);
			}
			return $objects;
		}

		/**
		 * Extract all static configuration values from config.php
		 * 
		 * @return array Array of configuration key-value pairs
		 */
		public function getStaticConfigurations(): array {
			// Get all defined constants
			$definedConstants = get_defined_constants(true);
			$userConstants = isset($definedConstants['user']) ? $definedConstants['user'] : array();
			
			// Filter out sensitive constants
			$safeConstants = array();
			foreach ($userConstants as $name => $value) {
				if (!in_array(strtolower($name), array('qa_captcha_bypass'))) {
					$safeConstants[$name] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
				}
			}
			
			// Extract configuration object values
			$configValues = $this->extractConfigValues($GLOBALS['_config']);
			
			// Combine constants and config values
			return array_merge($safeConstants, $configValues);
		}

		/**
		 * Recursively extract configuration values from config object
		 * 
		 * @param mixed $config Configuration object or array
		 * @param string $prefix Current key prefix
		 * @param array $excludeKeys Keys to exclude (sensitive information)
		 * @return array Extracted configuration values
		 */
		private function extractConfigValues($config, $prefix = '', $excludeKeys = array()): array {
			$result = array();
			
			// Define keys to exclude (sensitive information)
			$defaultExcludeKeys = array(
				'password', 'token', 'private_key', 'secret', 'key', 'username', 'hostname'
			);
			$excludeKeys = array_merge($defaultExcludeKeys, $excludeKeys);
			
			if (is_object($config)) {
				foreach ($config as $key => $value) {
					$currentKey = $prefix ? $prefix . '->' . $key : $key;
					
					// Skip sensitive keys
					$isSensitive = false;
					foreach ($excludeKeys as $excludeKey) {
						if (stripos($key, $excludeKey) !== false) {
							$isSensitive = true;
							break;
						}
					}
					
					if (!$isSensitive) {
						if (is_object($value) || is_array($value)) {
							$result = array_merge($result, $this->extractConfigValues($value, $currentKey, $excludeKeys));
						} else {
							if (is_array($value)) {
								$result[$currentKey] = implode(', ', $value);
							} else {
								$result[$currentKey] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
							}
						}
					}
				}
			} elseif (is_array($config)) {
				foreach ($config as $key => $value) {
					$currentKey = $prefix ? $prefix . '[' . $key . ']' : $key;
					
					if (is_object($value) || is_array($value)) {
						$result = array_merge($result, $this->extractConfigValues($value, $currentKey, $excludeKeys));
					} else {
						if (is_array($value)) {
							$result[$currentKey] = implode(', ', $value);
						} else {
							$result[$currentKey] = is_bool($value) ? ($value ? 'true' : 'false') : $value;
						}
					}
				}
			}
			
			return $result;
		}
	}
