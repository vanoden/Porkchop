<?php
	namespace Site;

	class ModuleList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Site\Module';
		}

		public function findAdvanced($parameters, $advanced, $controls): array {
			$this->clearError();
			$this->resetCount();

			if (! is_dir(MODULES)) {
				$this->error("No modules patch defined");
				return [];
			}

			# Get Modules From MODULE folder
			if ($handle = opendir(MODULES)) {
				$modules = array();
				while (false !== ($module_name = readdir($handle))) {
					if (preg_match('/^[\w\-\_]+$/',$module_name)) {
						if (isset($parameters['name'])) {
							if (strtolower($parameters['name']) != strtolower($module_name)) continue;
						}

						$module = new Module();
						if ($module->get($module_name)) {
							$module->caseName($module_name);
							array_push($modules,$module);
							$this->incrementCount();
						}
						elseif ($module->error()) {
							$this->error($module->error());
							return [];
						}
						else {
							$this->error("Unhandled exception");
							return [];
						}
					}
				}
				return $modules;
			}
			else {
				$this->error("Cannot view modules data");
				return [];
			}
		}
	}
