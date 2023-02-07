<?php
	namespace Content\Template;
	
	class Shell Extends \BaseClass {
		private $_content;
		private $_params = array();
		private $_groups = array();

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
						app_log("Loaded template ".$argument);
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
			if (isset($content)) {
				while (preg_match('/\@\{(\w+)\}(.*)\@\{\-\w+\}/s',$content,$matches)) {
					$this->_groups[$matches[1]] = $this->_newGroup($matches[2]);

					$content = preg_replace('/\@\{\w+\}.*\@\{\-\w+\}/s','@{-'.$matches[1].'-}',$content,1);
				}
			}
			$this->_content = $content;
			return $this->_content;
		}

		protected function _newGroup($content) {
			$group = new \Content\Template\Shell\Group();
			$group->content($content);
			return $group;
		}

		public function group($name) {
			if (!isset($this->_groups[$name])) {
				$this->_groups[$name] = new \Content\Template\Shell\Group();
			}
			return $this->_groups[$name];
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

		// Replace Template Tags with Suppied Parameters
		// Return text to calling method
		private function _process() {
			$module_pattern = '/\$\{(\w[\w\-\.\_\-]+)\}/is';

			$output = $this->_content;

			// Replace Entire Lines
			foreach ($this->_groups as $name => $group) {

				$group_content = '';

				if (preg_match('/\@\{\-('.$name.')\-\}/',$output,$matches)) {

					$lines = $group->lines();

					foreach ($lines as $line) {
						$group_content .= $line->render();
					}
					$output = str_replace($matches[0],$group_content,$output);
				}
			}

			// Replace Individual Fields
			while (preg_match($module_pattern,$output,$matched)) {
				$search = $matched[0];
				$parse_message = "Replaced $search";
				$replace_start = microtime(true);
				$replace = $this->_replace($matched[1]);
				app_log($parse_message." with $replace in ".sprintf("%0.4f",(microtime(true) - $replace_start))." seconds",'info',__FILE__,__LINE__);
				$output = str_replace($search,$replace,$output);
			}

			# Return Messsage
			return $output;
		}

		private function _replace($string) {
			if (isset($this->_params[$string])) return $this->_params[$string];
			else return null;
		}

		// Return array of non-group fields
		public function fields() {
			preg_match_all('/\$\{(\w+\.\w+)\}/',$this->_content,$matches);
			array_shift($matches[1]);
			return $matches[1];
		}

		// Deprecated Function - Use render() instead
		public function output() {
			return $this->_process();
		}

		// Pass through to private method _process
		public function render() {
			return $this->_process();
		}
	}
