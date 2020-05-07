<?php
	require_once THIRD_PARTY."/paragonie/random_compat/lib/random.php";

	class Random {
		public $_error;
		private $_prefix;
		private $_keyspace = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		private $_case = 'upper';
		
		public function numeric() {
			$this->_keyspace = '0123456789';
		}
		public function alpha() {
			$this->_keyspace = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
		public function alphanumeric() {
			$this->_keyspace = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		}
		public function hex() {
			$this->_keyspace = '0123456789ABCDE';
		}
		public function lower() {
			$this->_case = 'lower';
		}
		public function upper() {
			$this->_case = 'upper';
		}
		public function setPrefix($prefix) {
			$this->_prefix = $prefix;
		}
		public function code($length = 16) {
			$keyspace = $this->_keyspace;
			$chars = array();

			$max = strlen($keyspace) -1;
			for ($i = 0; $i < $length; $i ++) {
				try {
					$int = random_int(0, $max);
				} catch (TypeError $e) {
					$this->_error = "Type Error Generating Code";
					return null;
				} catch (Error $e) {
					$this->_error = "Error Generating Code";
					return null;
				} catch (Exception $e) {
					$this->_error = "Exception Generating Code";
					exit;
				}
				array_push($chars,$keyspace[$int]);
			}
			$string = $this->_prefix.implode('',$chars);
			if ($this->_case == 'lower') return strtolower($string);
			else return strtoupper($string);
		}

		public function code16() {
			$length = 16;
			$keyspace = $this->_keyspace;
			$chars = array();

			$max = strlen($keyspace) -1;
			for ($i = 0; $i < $length; $i ++) {
				try {
					$int = random_int(0, $max);
				} catch (TypeError $e) {
					print "Error";
					exit;
				} catch (Error $e) {
					print "Error";
					exit;
				} catch (Exception $e) {
					print "Error";
					exit;
				}
				array_push($chars,$keyspace[$int]);
			}
			$string = implode('',$chars);
			$string = substr($string,0,4)."-".substr($string,4,4)."-".substr($string,8,4)."-".substr($string,12,4);
			if ($this->_case == 'lower') return strtolower($string);
			else return strtoupper($string);
		}
	}
