<?php
	class APIResponse Extends \HTTP\Response {
		protected $_data = array();
		public bool $success = true;

		public function success(bool $value = null): bool {
			if (isset($value)) $this->success = $value;
			return $this->success;
		}

		public function data(array $data) {
			$this->_data = $data;
		}

		public function addElement($name,$object) {
			$this->$name = $object;
		}

		public function print($format = 'xml') {
			$data = new \stdClass();
			foreach ($this as $key => $value) {
				if ($key == 'success') {
					if ($this->success()) $value = 1;
					else $value = 0;
				}
				elseif ($key == '_code') {
					http_response_code($value);
					continue;
				}
				elseif (preg_match('/^_/',$key)) continue;
				$data->$key = $value;
			}

			if ($format == 'json' || (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json')) {
				$format = 'json';
				header('Content-Type: application/json');
			}
			else {
				$format = 'xml';
				header('Content-Type: application/xml');
			}

			$document = new \Document($format);
			$document->prepare($data);
			if (isset($GLOBALS['_config']->site->force_content_length) && $GLOBALS['_config']->site->force_content_length == true) {
				$content = $document->content();
				header('Content-Length: '.strlen($content));
				print $content;
			}
			else {
				print $document->content();
			}
		}
	}
?>