<?php
	namespace Database;

	class Service Extends \BaseClass {
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
