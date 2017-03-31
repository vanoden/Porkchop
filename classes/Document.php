<?
	class Document {
		public $error;
		public $type;
		public $content;
		public $stylesheet;

		public function __construct($type = 'xml') {
			$this->type = $type;
		}

		public function prepare($object) {
			if ($this->type == 'xml') {
				$this->content = $this->_xmlout($object);
				return;
			}
			elseif ($this->type == 'json') {
				$this->content = $this->_jsonout($object);
				return;
			}
			else {
				$this->error = "Invalid document type";
				return undef;
			}
		}

		private function _xmlout($object) {
			require 'XML/Unserializer.php';
			require 'XML/Serializer.php';
			$options = array(
				XML_SERIALIZER_OPTION_INDENT        => '    ',
				XML_SERIALIZER_OPTION_RETURN_RESULT => true,
				XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
				'rootName'							=> 'opt',
			);
			$xml = &new XML_Serializer($options);
			if ($xml->serialize($object)) {
				//error_log("Returning ".$xml->getSerializedData());
				$output = $xml->getSerializedData();
				if ($this->stylesheet) {
					$output = '<?xml-stylesheet type="text/xsl" href="'.$this->stylesheet.'"?>'."\n".$output;
				}
				return $output;
			}
		}

		private function _jsonout($object) {
			return json_encode($object,JSON_PRETTY_PRINT);
		}

		public function content() {
			return $this->content;
		}
	}
?>