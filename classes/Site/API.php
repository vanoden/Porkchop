<?php
	namespace Site;

	class API {
		protected $_error;
		protected $response;
		protected $module;
		protected $_admin_role = 'administrator';
		protected $_default_home = '/';
		protected $_schema;
		protected $_version;
		protected $_name;
		protected $_release;

		public function __construct() {
			$this->response = new \HTTP\Response();
		}

		public function admin_role() {
			return $this->_admin_role;
		}
		public function default_home() {
			return $this->_default_home;
		}

		###################################################
		### Just See if Server Is Communicating			###
		###################################################
		public function ping() {
			$response = new \HTTP\Response();
			$response->header->session = $GLOBALS['_SESSION_']->code;
			$response->header->method = $_REQUEST["method"];
			$response->header->date = $this->system_time();
			$response->message = "PING RESPONSE";
			$response->success = 1;
	
			api_log($response);
			print $this->formatOutput($response);
		}

		###################################################
		### System Time									###
		###################################################
		private function system_time() {
			return date("Y-m-d H:i:s");
		}
		###################################################
		### Return Properly Formatted Error Message		###
		###################################################
		public function error($message) {
			$_REQUEST["stylesheet"] = '';
			error_log($message);
			$response->message = $message;
			$response->success = 0;
			print $this->formatOutput($response);
			exit;
		}

		###################################################
		### Application Error							###
		###################################################
		public function app_error($message,$file = __FILE__,$line = __LINE__) {
			app_log($message,'error',$file,$line);
			$this->error('Application Error');
		}
		###################################################
		### Convert Object to XML						###
		###################################################
		public function formatOutput($object) {
			if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
				$format = 'json';
				header('Content-Type: application/json');
			}
			else {
				$format = 'xml';
				header('Content-Type: application/xml');
			}
			$document = new \Document($format);
			$document->prepare($object);
			return $document->content();
		}

		# Manage Module Schema
		public function schemaVersion() {
			if ($this->_schema->error) {
				$this->app_error("Error getting version: ".$this->_schema->error,__FILE__,__LINE__);
			}
			$version = $this->_schema->version();
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->version = $version;
			print $this->formatOutput($response);
		}
		public function schemaUpgrade() {
			if ($this->_schema->error) {
				$this->app_error("Error getting version: ".$this->_schema->error,__FILE__,__LINE__);
			}
			$version = $this->_schema->upgrade();
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->version = $version;
			print $this->formatOutput($response);
		}
		public function _form() {
			$form = '';
			$methods = $this->_methods();

			$cr = "\n";
			$t = "\t";
			foreach ($methods as $name => $params) {
				$form .= $t.'<form method="post" action="/_'.$this->_name.'/api" name="'.$name.'">'.$cr;
				$form .= $t.$t.'<input type="hidden" name="method" value="'.$name.'" />'.$cr;
				$form .= $t.$t.'<div class="apiMethod">'.$cr;
				$form .= $t.$t.'<div class="h3 apiMethodTitle">'.$name.'</div>'.$cr;
				foreach ($params as $param => $options) {
					if ($options['required']) $required = ' required';
					else $required = '';
					if (isset($options['default'])) $default = $options['default'];
					else $default = '';
					$form .= $t.$t.$t.'<div class="apiParameter">'.$cr;
					$form .= $t.$t.$t.$t.'<span class="label apiLabel'.$required.'">'.$param.'</span>'.$cr;
					$form .= $t.$t.$t.$t.'<input type="text" id="'.$param.'" name="'.$param.'" class="value input apiInput" value="'.$default.'" />'.$cr;
					$form .= $t.$t.$t.'</div>'.$cr;
				}
				$form .= $t.$t.$t.'<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>'.$cr;
				$form .= $t.$t.'</div>'.$cr;
				$form .= $t.'</form>'.$cr;
			}
			return $form;
		}
	}
?>