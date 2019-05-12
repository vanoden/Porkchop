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
				if (preg_match('/^([\w\-]+)\:([\w\-]+)/',$output[0],$matches)) {
					$this->_type = $matches[1];
					$this->_code = $matches[2];
					return true;
				}
				else {
					$this->_error = "Cannot parse positive response: '".$output[0]."'";
					return false;
				}
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
?>
