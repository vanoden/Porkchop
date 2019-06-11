<?
	namespace Site;

	class ModuleList {
		private $_error;
		private $_count = 0;

		public function find($parameters = array()) {
			if (! is_dir(MODULES)) {
				$this->_error = "No modules patch defined";
				return null;
			}

			# Get Modules From MODULE folder
			if ($handle = opendir(MODULES)) {
				$modules = array();
				while (false !== ($module_name = readdir($handle))) {
					if (preg_match('/^[\w\-\_]+$/',$module_name)) {
						if (isset($parameters['name'])) {
							if ($parameters['name'] != $module_name) continue;
						}

						$module = new Module();
						if ($module->get($module_name)) {
							array_push($modules,$module);
							$this->_count ++;
						}
						elseif ($module->error) {
							$this->_error = $module->error;
							return null;
						}
						else {
							$this->_error = "Unhandled exception";
							return null;
						}
					}
				}
				return $modules;
			}
			else {
				$this->error = "Error in Site::Module::find(): Cannot view modules data";
				return null;
			}
		}

		public function error() {
			return $this->_error;
		}

		public function count() {
			return $this->_count;
		}
	}
?>
