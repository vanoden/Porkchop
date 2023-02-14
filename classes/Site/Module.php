<?php
	namespace Site;

	class Module Extends \BaseClass {

		private $_name;
		private $_path;
		private $_metadata;

		public function __construct() {
			$this->_metadata = new \stdClass();
    		parent::__construct();			
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
			$this->getMetadata();

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
		public function getMetadata() {
			$metadata = new \stdClass();
			$path = $this->_path."/metadata.xml";
			if (is_file($path)) {
				require_once 'XML/Unserializer.php';
				$options = array(
					XML_SERIALIZER_OPTION_RETURN_RESULT => true,
					XML_SERIALIZER_OPTION_MODE          => 'simplexml',
				);
				$xml = new \XML_Unserializer($options);
				if ($xml->unserialize($path,true,$options)) {
					$this->_metadata = (object) $xml->getUnserializedData();
				}
        	}
			return true;
		}
		public function name() {
			return $this->_name;
		}
		public function description() {
			if (isset($this->_metadata->description)) return $this->_metadata->description;
			return null;
		}

		public function validName($string): bool {
			return $this->validCode($string);
		}
	}
