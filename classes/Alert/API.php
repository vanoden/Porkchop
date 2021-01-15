<?php
	namespace Alert;

    // Base Class for APIs
	class API extends \API {

		public function __construct() {
			$this->_name = 'alert';
			$this->_version = '0.1.1';
			$this->_release = '2021-01-14';
			$this->_schema = new \Action\Schema();
			parent::__construct();
		}
		
		public function foo() {
			api_log('content',$_REQUEST['bar'],$response);
				
			// Send Response
			print $this->formatOutput('bar');
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'foo'	=> array(
                    'bar'	=> array('required' => true),
				)
			);
		}

	}
