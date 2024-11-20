<?php
	class Document Extends \BaseClass {
		public $type;
		public $_stylesheet;
		private $_content;
		private $_data;

		public function __construct($type = 'xml') {
			$this->type = $type;
		}

		public function stylesheet($string) {
			$this->_stylesheet = $string;
			return $this->_stylesheet;
		}

		public function prepare($object) {
            $object = $this->filterElements($object);
			if ($this->type == 'xml') {
				$this->_content = $this->_xmlout(json_decode(json_encode($object)));
				return;
			}
			elseif ($this->type == 'json') {
				$this->_content = $this->_jsonout($object);
				return;
			}
			elseif ($this->type == 'yaml') {
				$this->_content = yaml_emit($object);
				return;
			}
			else {
				$this->error("Invalid document type");
				return null;
			}
		}

		public function parse($string) {
			if ($this->type == 'xml') {
				$this->_xmlin($string);
			}
			return true;
		}
		
		private function _xmlin($string) {
			require_once 'XML/Unserializer.php';
			$unserializer = new XML_Unserializer();
			$unserializer->unserialize($string);
			$this->_data = $unserializer->getUnserializedData();
		}

		private function _xmlout($object) {
			require_once 'XML/Unserializer.php';
			require_once 'XML/Serializer.php';
			$options = array(
				XML_SERIALIZER_OPTION_INDENT        => '    ',
				XML_SERIALIZER_OPTION_RETURN_RESULT => true,
				XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
				'rootName'							=> 'opt',
			);
			$xml = new XML_Serializer($options);
			if ($xml->serialize($object)) {
				//error_log("Returning ".$xml->getSerializedData());
				$output = $xml->getSerializedData();
				if (!empty($this->_stylesheet)) {
					$output = '<?xml-stylesheet type="text/xsl" href="'.$this->_stylesheet.'"?>'."\n".$output;
				}
				return $output;
			}
		}

		private function _jsonout($object) {
			return json_encode($object,JSON_PRETTY_PRINT);
		}

		public function content() {
			return $this->_content;
		}
		
		public function data() {
			return $this->_data;
		}

        protected function filterElements($object) {
            if (is_array($object)) {
                foreach ($object as $key=>$value) {
                    if (is_object($value)) {
                        $object[$key] = $this->filterElements($value);
                    }
                }
                return $object;
            }
            else {
                foreach ($object as $key=>$value) {
                    if ($key == '_tableName') {
                        unset($object->$key);
                    }
                    elseif ($key == '_tableNumberColumn') {
                        unset($object->$key);
                    }
                    elseif (is_object($value)) {
                        $object->$key = $this->filterElements($value);
                    }
                }
                return $object;
            }
        }
	}
