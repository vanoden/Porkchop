<?php
	namespace Database;

	class Service Extends \BaseClass {
		private $_connection;
		private $_params = array();

		public function __construct() {
			$this->_connection = $GLOBALS['_database'];
		}

		public function AddParam($value) {
			array_push($this->_params,$value);
		}

		public function Execute($query,$bind_params = null) {
			if (is_array($bind_params)) $this->_params = array_merge($this->_params,$bind_params);
			query_log($query,$this->_params,true);
			return new \Database\RecordSet($this->_connection->Execute($query,$this->_params));
		}

		public function ErrorMsg() {
			return $this->_connection->ErrorMsg();
		}

		public function Insert_ID() {
			return $this->_connection->Insert_ID();
		}

		public function version() {
			// Put Query to Get Version Here
			return $GLOBALS['_database']->_connectionID->server_info;
		}

		public function supports_password() {
			if ($this->version_compare('5.7.5',$this->version())) return false;
			return true;
		}

		public function version_compare($ver1,$ver2) {
			if (preg_match('/^(\d+)\.(\d+)\.(\d+)/',$ver1,$matches)) {
				$major1 = $matches[1];
				$minor1 = $matches[2];
				$patch1 = $matches[3];
			
				if (preg_match('/^(\d+)\.(\d+)\.(\d+)/',$ver2,$matches)) {
					$major2 = $matches[1];
					$minor2 = $matches[2];
					$patch2 = $matches[3];

					if ($major2 > $major1) return true;
					if ($major2 == $major1) {
						if ($minor2 > $minor1) return true;
						if ($minor2 == $minor1) {
							if ($patch2 >= $patch1) return true;
						}
					}
				}
			}
			return false;
		}
	}
