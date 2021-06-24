<?php
	namespace Content\Template;
	
	class Shell {
		private $_error;
		private $_content;
		private $_params = array();
		
		public function __construct($argument = null) {
			if (gettype($argument) == 'array') {
				if ($argument['path']) {
					if ($this->load($argument['path'])) {
						app_log("Loaded template ".$argument['path']);
						if($argument['parameters']) {
							app_log("Adding parameters ".print_r($argument['parameters'],true));
							$this->addParams($argument['parameters']);
						}
					}
					else {
						$this->_error = "Template file '".$argument['path']."' not found";
					}
				}
			}
			elseif (gettype($argument) == 'string') {
				if (file_exists($argument)) {
					if ($this->load($argument)) {
						app_log("Loaded template ".$argument['path']);
					}
				}
			}
		}

		public function load($path) {
			if (file_exists($path)) {
				if ($this->_content = file_get_contents($path)) {
					return true;
				}
				else {
					return false;
				}
			}
		}

		public function content($content = null) {
			if (isset($content)) $this->_content = $content;
			return $this->_content;
		}

		public function addParam($key,$value) {
			$this->_params[$key] = $value;
		}

		public function addParams($params = array()) {
			app_log("addParams(".print_r($params,true).")");
			foreach ($params as $key=>$value) {
				app_log("Adding param $key = $value");
				$this->addParam($key,$value);
			}
		}

		private function _process($message) {
			$module_pattern = '/\$\{([\w\-\.\_\-]+)\}/is';

			while (preg_match($module_pattern,$message,$matched)) {
				$search = $matched[0];
				$parse_message = "Replaced $search";
				$replace_start = microtime(true);
				$replace = $this->_replace($matched[1]);
				app_log($parse_message." with $replace in ".sprintf("%0.4f",(microtime(true) - $replace_start))." seconds",'trace',__FILE__,__LINE__);
				$message = str_replace($search,$replace,$message);
			}

			# Return Messsage
			return $message;
		}

		private function _replace($string) {
			return $this->_params[$string];
		}

		public function output() {
			return $this->_process($this->_content);
		}
		
		public function error() {
			return $this->_error;
		}
	}
