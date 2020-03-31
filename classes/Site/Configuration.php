<?php
	namespace Site;
	
	class Configuration {
		private $key;
		private $value;
		private $_error;

		public function __construct($key = null) {
			if (isset($key)) {
				$this->key = $key;
				$this->get($key);
			}
		}

		public function delete() {
			$unset_config_query = "
				DELETE
				FROM	site_configurations
				WHERE	`key` = ?
			";
			query_log($unset_config_query,array($this->key),true);
			$GLOBALS['_database']->Execute($unset_config_query,array($this->key));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Site::Configuration::unset(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			else {
				$this->value = $value;
				app_log("Set ".$this->key." to $value");
				return true;
			}
		}

		public function set($value) {
			$set_config_query = "
				INSERT
				INTO	site_configurations
				(	`key`,`value`)
				VALUES 	(?,?)
				ON DUPLICATE KEY UPDATE
					`value` = ?
			";
			query_log($set_config_query,array($this->key,$value,$value),true);
			$GLOBALS['_database']->Execute($set_config_query,array($this->key,$value,$value));
			if ($GLOBALS['_database']->ErrorMsg()) {
				$this->_error = "SQL Error in Site::Configuration::set(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			else {
				$this->value = $value;
				app_log("Set ".$this->key." to $value");
				return true;
			}
		}

		public function get($key) {
			$get_config_query = "
				SELECT	`key`,`value`
				FROM	site_configurations
				WHERE	`key` = ?
			";
			query_log($get_config_query,array($key),true);
			$rs = $GLOBALS['_database']->Execute($get_config_query,array($key));
			if (! $rs) {
				$this->_error = "SQL Error in Site::Configuration::get(): ".$GLOBALS['_database']->ErrorMsg();
				return false;
			}
			list($this->key,$this->value) = $rs->FetchRow();
			if (empty($this->key)) {
				app_log("No Record in DB, Checking Config Global");
				if (isset($GLOBALS['_config']->{$key})) {
					$this->key = $key;
					$this->value = $GLOBALS['_config']->{$key};
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

		public function error() {
			return $this->_error;
		}
	}
