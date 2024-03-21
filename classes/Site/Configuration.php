<?php
	namespace Site;
	
	class Configuration Extends \BaseModel {

		public $key;
		public $value;

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
				return true;
			}
		}
		
		public function get($key): bool {
			$this->clearError();
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
				app_log("No Record in DB, Checking Config Global");
				if (isset($GLOBALS['_config']->site->{$key})) {
					$this->key = $key;
					$this->value = $GLOBALS['_config']->site->{$key};
					return true;
				}
				else {
					$this->key = $key;
					$this->value = null;
					return false;
				}
			}
			else {
				app_log("Config record ".$this->key." found with ".$this->value);
				return true;
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

            // Clear Cache to Allow Update
			$cache = $this->cache();
			if (isset($cache)) $cache->delete();

            return true;
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
			if (preg_match('/^\w[\w\.\-\_\s]*$/',$string)) return true;
			else return false;
		}

		public function validValue($string) {
			return true;
		}
	}
