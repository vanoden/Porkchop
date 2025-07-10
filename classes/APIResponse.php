<?php
	class APIResponse Extends \HTTP\Response {
		protected $_data = array();
		public bool $success = true;
		protected $_stylesheet = null;
		protected $_method = null;
		protected $_api = null;
		private bool $_showDetails = false;

		public function __construct() {
			parent::__construct();
			$this->_code = 200;

			// Identify Calling Endpoint and Method
			$backtrace = debug_backtrace();
			if (isset($backtrace[1]['class']) && isset($backtrace[1]['function'])) {
				$this->_api = $backtrace[2]['class'];
				$this->_method = $backtrace[2]['function'];
			}
			else {
				$this->_api = 'Unknown';
				$this->_method = 'Unknown';
			}
		}

		public function success($value = null): bool {
			if (isset($value) && is_bool($value)) $this->success = $value;
			return $this->success;
		}

		public function data(array $data) {
			$this->_data = $data;
		}

		public function stylesheet($string) {
			$this->_stylesheet = $string;
		}

		public function addElement($name,$object) {
			$this->$name = $object;
		}

		public function showDetails(bool $show = true) {
			$this->_showDetails = $show;
		}

		public function print($format = 'xml') {
			$comm = new \Monitor\Communication();

			$data = new \stdClass();

			if ($this->_showDetails || (isset($_REQUEST['_showdetails']) && ($_REQUEST['_showdetails'] == 'true' || $_REQUEST['_showdetails'] == '1'))) {
				$data->request_details = new \stdClass();
				$data->request_details->api = $this->_api;
				$data->request_details->method = $this->_method;
			}
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

			// Show stylesheet if set
			if (!empty($this->_stylesheet)) {
				$document->stylesheet($this->_stylesheet);
			}

			// Add Data to Document
			$document->prepare($data);

			// Store Document in Communication Record
			$comm->update(json_encode($document));

			// Specify content length if configured
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