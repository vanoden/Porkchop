<?php
	namespace Site;

	class Module Extends \BaseModel {

		private $_name;
		private $_path;
		private $_metadata;
		private $_caseName;

		public function __construct($id = 0) {
			$this->_tableName = 'site_modules';
			parent::__construct($id);
			$this->_metadata = new \stdClass();
		}

		public function import_metadata($module) {
			$metadata = $this->getMetaData($module);

			# Loop through Roles
			print_r($metadata);
		}

		public function get($name): bool {
			if (! preg_match('/^\w[\w\-\_]*$/',$name)) {
				$this->error("Invalid module name");
				return false;
			}
			if (! is_dir(MODULES."/".$name)) {
				$this->error("Module not found");
				return false;
			}
			$this->_name = $name;
			$this->_path = MODULES."/".$this->_name."/".$this->style();

			return true;
		}
		public function style() {
			if (isset($GLOBALS['_config']->style[$this->_name])) return $GLOBALS['_config']->style[$this->_name];
			else return 'default';
		}
		public function views() {
			if (! $this->_name) {
				$this->error("Module not identified");
				return null;
			}
			if (! is_dir($this->_path)) {
				$this->error("Module style not found"); 
				return null;
			}
			if ($handle = opendir($this->_path)) {
				$views = array();
				while (false !== ($view = readdir($handle))) {
					if (preg_match('/^([\w\-\_])_mc\.php$/',$view,$matches)) {
						array_push($views,$views[1]);
					}
				}
				return $views;
			}
			else {
				$this->error("Cannot view view data");
				return null;
			}
		}

		/**
		 * Get the name of the module
		 * @return mixed
		 */
		public function name() {
			return $this->_name;
		}

		/**
		 * Get/Set the name with cases matching file path
		 * @return mixed|null 
		 */
		public function caseName($name = null): ?string {
			if (!empty($name)) {
				$this->_caseName = $name;
			}
			return $this->_caseName;
		}

		/**
		 * Get the description of the module
		 * @return mixed
		 */
		public function description() {
			if (isset($this->_metadata->description)) return $this->_metadata->description;
			return null;
		}

		/**
		 * See If Name is Valid for a Module
		 * @param mixed $string Name
		 * @return bool True if valid name
		 */
		public function validName($string): bool {
			return $this->validCode($string);
		}
	}
