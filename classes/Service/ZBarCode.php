<?php
	namespace Service;

	class ZBarCode {
		private $_error;
		private $_binary;
		private $_available = false;

		public function __construct() {
			$this->_binary = '/usr/bin/zbarimg';
			if (! file_exists($this->_binary)) {
				$this->_error = "zbarimg binary not found";
			}
			else {
				$version = exec($this->_binary." --version");
				if (! preg_match('/^0.10$/',$version)) {
					$this->_error = "binary not supported";
				}
				else {
					$this->_available = true;
				}
			}
		}

		public function readBarCode($file) {
			if (! $this->_available) {
				$this->_error = "Binary not available";
				return false;
			}

			if (! file_exists($file)) {
				$this->_error = "File not found";
				return false;
			}

			exec($this->_binary." ".$file.' 2>&1', $output, $return);
			if ($return == 0) {
				foreach ($output as $record) {
					if (preg_match('/^([A-Z]{3,4}\-\d+)\:([\w\-]+)/',$record,$matches)) {
						app_log("Got code: ".$matches[0]);
						$this->_type = $matches[1];
						$this->_code = $matches[2];
						return true;
					}
					if (preg_match('/^(I2\/\d)\:([\w\-]+)/',$record,$matches)) {
						app_log("Got code: ".$matches[0]);
						$this->_type = $matches[1];
						$this->_code = $matches[2];
						return true;
					}
					else {
						app_log("Record $record not parseable");
					}
				}
				$this->_error = "Cannot parse positive response: '".end($output)."'";
				app_log("Unscannable bar code: ".print_r($output,true),'notice');
				return false;
			}
			else {
				$this->_error = $output[0];
				return false;
			}
		}

		public function type() {
			return $this->_type;
		}

		public function code() {
			return $this->_code;
		}

		public function error() {
			return $this->_error;
		}
	}
