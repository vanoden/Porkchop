<?php
	namespace Site;
	
	class Configuration Extends \BaseClass {

		public $key;
		public $value;

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
			$database->addParam($this->key);
			$database->addParam($value);
			$database->addParam($value);
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
			$database->addParam($key);
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
