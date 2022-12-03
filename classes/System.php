<?php
	class System Extends \BaseClass {
		private $_version = "0.9.1";

		public function operatingSystem() {
			return PHP_OS;
		}

		public function uptime() {
			$uptime = @file_get_contents("/proc/uptime");
			$uptime = explode(" ",$uptime)[0];
			return $uptime;
		}

		public function version() {
			return $this->_version;
		}
	}