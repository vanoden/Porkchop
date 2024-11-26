<?php
	/* Base Class for Site APIs */
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
		protected $_communication;
		private $page;

		public function __construct() {
			if (!empty($_REQUEST["method"])) {
				$counterKey = "api.".$this->_name.".".$_REQUEST["method"];
				$counter = new \Site\Counter($counterKey);
				$counter->increment();
				app_log("API: ".$this->_name.".".$_REQUEST['method']);
			}
			$site = new \Site();
			$this->page = $site->page();
			$this->module = $this->page->module();
			$this->response = new \HTTP\Response();
			$this->_communication = new \Monitor\Communication();
		}

		/********************************************/
		/* Show be overridden by child, but return	*/
		/* an array if not.							*/
		/********************************************/
		public function _methods() {
			return array();
		}

		public function admin_role() {
			return $this->_admin_role;
		}

		public function default_home() {
			return $this->_default_home;
		}

		/********************************************/
		/* Just See if Server Is Communicating		*/
		/********************************************/
		public function ping() {
			$response = new \APIResponse();
			$header = new stdClass();
			$header->session = $GLOBALS['_SESSION_']->code;
			$header->method = $_REQUEST["method"];
			$header->date = $this->system_time();
			$response->addElement('header',$header);
			$response->addElement('message',"PING RESPONSE");
			$response->success(true);
			$response->print();
		}

		/********************************************/
		/* Return Error unless User Authenticated	*/
		/********************************************/
		public function requireAuth() {
			if (! $GLOBALS['_SESSION_']->authenticated()) $this->deny();
		}

		/********************************************/
		/* Return Error unless User has 			*/
		/* the required role.						*/
		/********************************************/
		public function requireRole($role_name) {
			if (! $GLOBALS['_SESSION_']->customer->has_role($role_name)) $this->deny();
		}

		/********************************************/
		/* Return Error unless User has the			*/
		/* required privilege.  If an array is 		*/
		/* passed, only one is required.			*/
		/* To require multiple privileges, call 	*/
		/* this function multiple times.			*/
		/********************************************/
		public function requirePrivilege($privilege_name) {
			if (is_array($privilege_name)) {
				// Ok if ANY privilege is matched
				foreach ($privilege_name as $privilege) {
					if ($GLOBALS['_SESSION_']->customer->can($privilege)) return;
				}
				$this->deny();
			}
			if (! $GLOBALS['_SESSION_']->customer->can($privilege_name)) $this->deny("Permission Denied");
		}

		/********************************************/
		/* Return Active Anti-CSRF Token			*/
		/********************************************/
		public function csrfToken() {
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('token',$GLOBALS['_SESSION_']->getCSRFToken());

			$comm = new \Monitor\Communication();
			$comm->update(json_encode($response));
	
			$response->print();
		}

		/************************************************/
		/* Validate Anti-CSRF Token						*/
		/************************************************/
		public function validCSRFToken() {
			// Machines don't send CSRF Token
			if (preg_match('/^portal_sync/',$_SERVER['HTTP_USER_AGENT'])) return true;

			// Not valid if token not even sent
			if (empty($_REQUEST['csrfToken'])) return false;

			// Check provided token against session
			if ($GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) return true;

			// All else fails, check failed
			return false;
		}

		/************************************************/
		/* Formatted System Time						*/
		/************************************************/
		private function system_time() {
			return date("Y-m-d H:i:s");
		}

		/************************************************/
		/* Send Properly Formatted Error Message		*/
		/************************************************/
		public function error($message) {
			$_REQUEST["stylesheet"] = '';
			error_log($message);

			$response = new \APIResponse();
			if (preg_match('/SQL\sError/',$message)) {
				$response->code(500);
				$message = "Application Data Error";
			}

			$counterKey = "api.".$this->_name.".".$_REQUEST['method'].".error";
			$errCounter = new \Site\Counter($counterKey);
			$errCounter->increment();

			$response->addElement('error',$message);
			$response->success(false);
			$response->print();
			exit;
		}

		/************************************************/
		/* Send Generic Internal Error Response			*/
		/* To Log and Hides Sensitive Errors			*/
		/************************************************/
		public function app_error($message,$file = __FILE__,$line = __LINE__) {
			app_log($message,'error',$file,$line);
			$this->error('Application Error');
		}

		/************************************************/
		/* Send Proper Auth Failure Response			*/
		/************************************************/
		public function auth_failed($reason,$message = null) {
			$this->_incrementCounter('incorrect');
			$_REQUEST["stylesheet"] = '';
			$response = new \APIResponse();
			$response->code(401);
			$response->success(false);
			if (!empty($message)) $response->addElement('error',$message);
			else $response->addElement('error',"Authentication Failed");
			http_response_code(401);
			$response->print();
			exit;
		}

		/************************************************/
		/* Send Proper Permission Denied Response		*/
		/************************************************/
		public function deny($message = null) {
			$_REQUEST["stylesheet"] = '';
			$response = new \APIResponse();
			$response->code(403);
			$response->success(false);
			if (!empty($message)) $response->addElement('error',$message);
			else $response->addElement('error',"Permission Denied");
			http_response_code(403);
			$response->print();
			exit;
		}

		/************************************************/
		/* Send Proper Incorrect Request Response		*/
		/************************************************/
		public function incompleteRequest($message = null) {
			$_REQUEST["stylesheet"] = '';
			$response = new \APIResponse();
			$response->code(422);
			$response->success(false);
			if (!empty($message)) $response->addElement('error',$message);
			else $response->addElement('error',"Unprocessable Request");
			http_response_code(422);
			$response->print();
			exit;
		}

		/************************************************/
		/* Send Proper Resource Not Found Response		*/
		/************************************************/
		public function notFound($message = null) {
			if (empty($message)) $message = "Resource not found";
			$_REQUEST["stylesheet"] = '';
			$response = new \APIResponse();
			$response->code(404);
			$response->success(false);
			$response->addElement('error',$message);
			$response->print();
			exit;
		}

		/************************************************/
		/* Send Proper Missing Requirement Response		*/
		/************************************************/
		public function invalidRequest($message = null) {
			if (empty($message)) $message = "Invalid Request";
			app_log("Invalid Request for ".class_name($this)." method ".$_REQUEST['method'].": ".$message,'error',__FILE__,__LINE__);
			$_REQUEST["stylesheet"] = '';
			$response = new \APIResponse();
			$response->code(400);
			$response->success(false);
			$response->addElement('error',$message);
			$response->print();
			exit;
		}

		/************************************************/
		/* Record API Communication for Debugging		*/
		/************************************************/
		public function _store_communication() {
			if (!array_key_exists('method',$_REQUEST)) return;
			$message = "Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code;
			if (array_key_exists('asset_code',$_REQUEST)) $message .= " for asset ".$_REQUEST['asset_code'];
			app_log($message,'debug',__FILE__,__LINE__);

			if ($_REQUEST['method'] == 'findLastCommunication') return;

			# Comm Dashboard
			$store_request = $GLOBALS['_REQUEST_'];
			$this_post = $_POST;
			unset($this_post['password']);
			$store_request->post = $this_post;
			$store_request->method = $_REQUEST["method"];

			$this->_communication->add(array(json_encode($store_request),'[PENDING]'));
			if ($this->_communication->error()) {
				app_log("Error in api comm storage: ".$this->_communication->error(),'error',__FILE__,__LINE__);
			}
		}

		/************************************************/
		/* Return the object as XML Model.				*/
		/* DEPRECATED - Use APIResponse::print()		*/
		/************************************************/
		public function formatOutput($object,$format = 'xml') {
			if ($format == 'json' || (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json')) {
				$format = 'json';
				header('Content-Type: application/json');
			}
			else {
				$format = 'xml';
				header('Content-Type: application/xml');
			}
			$document = new \Document($format);
			$document->prepare($object);
			if (isset($GLOBALS['_config']->site->force_content_length) && $GLOBALS['_config']->site->force_content_length == true) {
				$content = $document->content();
				header('Content-Length: '.strlen($content));
				return $content;
			}
			else {
				return $document->content();
			}
		}

		/************************************************/
		/* Call requested API function					*/
		/************************************************/
		public function method($function_name = null) {
			// What Module is this?
			$api_name = "\\".ucfirst($this->module)."\\API";
			$api = new $api_name();

			if (empty($function_name)) {
				if ($this->page->requireRole('API User') || $this->page->requireRole('Administrator')) {
					$this->apiMethods();
				}
				else {
					$this->deny();
				}
				$this->incompleteRequest("Missing method");
			}

			// Method Requirements
			$method = $api->_methods()[$function_name];
			if (isset($method['privilege_required']) && !(preg_match('/^\[\w+\]$/',$method['privilege_required']))) {
				$this->requirePrivilege($method['privilege_required']);
			}
			if (isset($method['authentication_required']) && $method['authentication_required']) {
				$this->requireAuth();
			}
			if (isset($method['token_required']) && $method['token_required']) {
				if (! $this->validCSRFToken()) {
					$this->invalidRequest("Invalid or missing CSRF Token");
				}
			}

			// Unset [NULL] values (Allows user to opt not to change value in form)
			foreach ($_REQUEST as $key => $value) {
				if ($value == '[NULL]') {
					$_REQUEST[$key] = null;
				}
			}

			// Enforce Individual Parameter Requirements
			foreach ($method['parameters'] as $param => $options) {
				if (!array_key_exists($param,$_REQUEST)) continue;
				$value = $_REQUEST[$param];
				// Temporarily remove wildcards for validation checks
				if (isset($options['allow_wildcards']) && $options['allow_wildcards']) {
					$value = str_replace('*','',$value);
					$value = str_replace('?','',$value);
				}
				//print_r($param."\n");
				if (isset($options['required']) && $options['required']) {
					//print_r("\trequired\n");
					if (empty($value)) {
						$this->incompleteRequest("Missing required parameter: $param");
					}
				}
				// Enforce Parameter Type Requirements
				if (!empty($value) && isset($options['content-type'])) {
					//print_r("\t".$options['content-type']."\n");
					if ($options['content-type'] == 'int' && ! is_numeric($value)) {
						$this->invalidRequest("Invalid $param value");
					}
					elseif ($options['content-type'] == 'boolean') {
						if ($_REQUEST[$param] == 1) $_REQUEST[$param] = 'true';
						elseif ($_REQUEST[$param] == 0) $_REQUEST[$param] = 'false';
						if ($_REQUEST[$param] != 'true' && $_REQUEST[$param] != 'false') {
							$this->invalidRequest("Invalid $param value");
						}
					}
				}
				// Enforce Parameter Regex Requirements
				if (!empty($_REQUEST[$param]) && isset($options['regex'])) {
					// NOTE: Use original values for Regex
					//print_r("\tRegex: ".$options['regex']."\n");
					if (! preg_match($options['regex'],$_REQUEST[$param])) {
						$this->invalidRequest("Invalid $param value");
					}
				}

				// Enforce Parameter Validation Method Requirements
				if (!empty($value) && isset($options['validation_method'])) {
					$validation_method = $options['validation_method'];
					if (preg_match("/(.*)\:\:([\w\_\-]+)\(\)/",$validation_method,$matches)) {
						$validation_method = $matches[2];
						$validation_class_name = '\\'.str_replace('::','\\',$matches[1]);
						$validation_class = new $validation_class_name();
						//print_r("\tValidation: ".$validation_class_name."->".$validation_method."(".$_REQUEST[$param].")\n");
						if (! $validation_class->$validation_method($value)) {
							$this->invalidRequest("Invalid $param value");
						}
					}
					else {
						$this->error("Validation method not found");
					}
				}
			}

			// Enforce Parameter Group Requirements
			$requirement_groups = array();
			foreach ($method as $param => $options) {
				if (isset($options['requirement_group']) && is_numeric($options['requirement_group'])) {
					if (! in_array($options['requirement_group'],$requirement_groups)) {
						$requirement_groups[] = $options['requirement_group'];
					}
				}
			}
			$found = true;
			foreach ($requirement_groups as $group_id) {
				$found = true;
				foreach ($method as $param => $options) {
					if ($options['requirement_group'] == $group_id) {
						if (empty($_REQUEST[$param])) {
							$found = false;
							break;
						}
					}
				}
				if ($found) break;
			}
			if (! $found) $this->incompleteRequest("Missing required parameter");

			$this->_store_communication();
			$this->log("Request: ".print_r($_REQUEST,true));
			if (! method_exists($api,$function_name)) {
				$this->notFound("Method not found");
			}
			$api->$function_name();
			$this->log("Response: ".print_r($this->response,true));
		}

		/************************************************/
		/* Return List of Available Methods				*/
		/************************************************/
		public function apiMethods() {
			$methods = $this->_methods();
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('method',$methods);
			$response->print();
		}

		/************************************************/
		/* Write an Event to the proper API Log			*/
		/************************************************/
		public function log($message) {
			if (! API_LOG) return false;
			$log = "";
			$module = $GLOBALS['_REQUEST_']->module;
			$login = $GLOBALS['_SESSION_']->customer->code;
			$method = $_REQUEST['method'];
			$host = $GLOBALS['_REQUEST_']->client_ip;
			$response = new \APIResponse();
			if (is_object($response) && $response->success()) $status = "SUCCESS";
			else $status = "FAILED";
			if (is_numeric($GLOBALS['_REQUEST_']->timer)) $elapsed = microtime() - $GLOBALS['_REQUEST_']->timer;
			else $elapsed = -1;

            if (is_dir(API_LOG))
                $log = fopen(API_LOG."/".$module.".log",'a');
            else
                $log = fopen(API_LOG,'a');

			fwrite($log,"[".date('m/d/Y H:i:s')."] $host $module $login $method $status $elapsed\n");
			fwrite($log,"_REQUEST: ".print_r($_REQUEST,true));
			fwrite($log,"_RESPONSE: ".print_r($response,true));
			fclose($log);
		}

		/************************************************/
		/* Get Database Schema 							*/
		/************************************************/
		public function schemaVersion() {
			if ($this->_schema->error) {
				$this->app_error("Error getting version: ".$this->_schema->error,__FILE__,__LINE__);
			}

			$version = $this->_schema->version();
			$response = new \APIResponse();
			$response->success(true);
			$response->addElement('version',$version);
			$response->print();
		}

		/************************************************/
		/* Run Database Schema Upgrade Function			*/
		/************************************************/
		public function schemaUpgrade() {
			if ($this->_schema->error) {
				$this->app_error("Error getting version: ".$this->_schema->error,__FILE__,__LINE__);
			}

			$response = new \APIResponse();
			if ($this->_schema->upgrade()) {
				$response->success(true);
				$response->addElement('version',$this->_schema->version());
			}
			else {
				$this->app_error("Error upgrading schema: ".$this->_schema->error,__FILE__,__LINE__);
			}
			$response->print();
		}

		// Increments Site Counter, not to be confused with BaseListClass::incrementCounter()
		public function _incrementCounter($reason) {
			$counterKey = "api.".$this->_name.".".$_REQUEST["method"].".".$reason;
			$counter = new \Site\Counter($counterKey);
			$counter->increment();
		}

		/**
		 * Build HTML Form for API Methods
		 */
		public function _form() {
			$api_name = "\\".ucfirst($this->module)."\\API";
			$api = new $api_name();
			$form = '';
			$methods = $api->_methods();

			$form .= '<h1>'.ucfirst($this->module).' API</h1>';

			$cr = "\n";
			$t = "\t";

			$token = $GLOBALS['_SESSION_']->getCSRFToken();
			foreach ($methods as $form_name => $settings) {
				$method = new \API\Method($settings);

				if ($method->hidden) continue;

				// See if method has file inputs
				$has_file_inputs = false;

				$parameters = $method->parameters();
				foreach ($parameters as $param) {
					if ($param->type == 'file') {
						$has_file_inputs = true;
						continue;
					}
				}
				if ($has_file_inputs) {
					$form .= $t.'<form method="post" action="/api/'.$api->_name.'" id="'.$form_name.'" name="'.$form_name.'" enctype="multipart/form-data">'.$cr;
				}
				else {
					$form .= $t.'<form method="post" action="/api/'.$api->_name.'" id="'.$form_name.'" name="'.$form_name.'">'.$cr;
				}
				$form .= $t.$t.'<input type="hidden" name="csrfToken" value="'.$token.'">'.$cr;
				$form .= $t.$t.'<input type="hidden" name="method" value="'.$form_name.'" />'.$cr;
				$form .= $t.$t.'<div class="apiMethod" onMouseOver="frameAPIFormBorder(this)" onMouseOut="unframeAPIFormBorder(this)">'.$cr;
				if ($method->deprecated) $form .= $t.$t.'<div class="h3 apiMethodTitle apiMethodDeprecated">'.$form_name." - deprecated!".'</div>'.$cr;
				else $form .= $t.$t.'<div class="h3 apiMethodTitle">'.$form_name.'</div>'.$cr;

				// Show Method Description if provided
				if ($method->description) {
					$form .= $t.$t.'<span class="apiMethodDescription">'.$method->description.'</span>'.$cr;
				}
				if ($method->verb) {
					$form .= $t.$t.'
					<div class="apiMethodSetting">
						<span class="label apiMethodSetting">URL</span>
						<span class="value apiMethodSetting">'.$method->verb.'</span>
					</div>'.$cr;
				}

				// Show Method Return Info if provided
				if ($method->return_element) {
					$form .= $t.$t.'
					<div class="apiMethodSetting">
						<span class="label apiMethodSetting">return_element</span>
						<span class="value apiMethodSetting">'.$method->return_element.'</span>
					</div>'.$cr;
				}
				if ($method->return_type) {
					$form .= $t.$t.'
					<div class="apiMethodSetting">
						<span class="label apiMethodSetting">return_type</span>
						<span class="value apiMethodSetting">'.$method->return_type.'</span>
					</div>'.$cr;
				}
				if ($method->return_mime_type) {
					$form .= $t.$t.'
					<div class="apiMethodSetting">
						<span class="label apiMethodSetting">return_mime_type</span>
						<span class="value apiMethodSetting">'.$method->return_mime_type.'</span>
					</div>'.$cr;
				}

				// Show Method Authentication Requirement
				$form .= '
					<div class="apiMethodSetting">
						<span class="label apiMethodSetting">Authentication Required</span>
						<span class="value apiMethodSetting">';
				if ($method->authentication_required || $method->privilege_required) $form .= "Yes";
				else $form .= "No";
				$form .= '
						</span>
					</div>'.$cr;

				// Show Method AntiCSRF Requirement
				$form .= '
					<div class="apiMethodSetting">
						<span class="label apiMethodSetting">AntiCSRF Token Required</span>
						<span class="value apiMethodSetting">';
				if ($method->token_required) $form .= "Yes";
				else $form .= "No";
				$form .= '
						</span>
					</div>'.$cr;

				// Show Method Privilege Requirement
				if (!empty($method->privilege_required)) $form .= $t.$t.'
					<div class="apiMethodSetting">
						<span class="label apiMethodSetting">Privilege Required</span>
						<span class="value apiMethodSetting">'.$method->privilege_required.'
						</span>
					</div>'.$cr;
				else $form .= $t.$t.'
					<div class="apiMethodSetting">
						<span class="label apiMethodSetting">Privilege Required</span>
						<span class="value apiMethodSetting">None</span>
					</div>'.$cr;

				// Add Parameters
				$parameters = $method->parameters();
				foreach ($parameters as $name => $parameter) {
					// Skip Hidden Parameters
					if ($parameter->hidden) continue;

					// Formatting for Required Fields
					if ($parameter->required) $required_class = ' required';
					elseif (!empty($parameter->requirement_group)) $required_class = ' required-group-'.$parameter->requirement_group;
					else $required_class = '';
					if ($parameter->deprecated) $required_class .= ' deprecated';

					// Initialize Default Value
					$default = $parameter->default;

					// Open Parameter Div Element
					$form .= $t.$t.$t.'<div class="apiParameter">'.$cr;

					// Open Label Span Element
					$form .= $t.$t.$t.$t.'<span class="label apiLabel'.$required_class.'" for="'.$name.'" onMouseOver="showAPIHelpMessage(this)" onMouseOut="hideAPIHelpMessage()">'.$name.'</span>'.$cr;

					// Populate Form Helper Values
					if (!empty($parameter->object)) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_object" value="'.addslashes($parameter->object).'"/>'.$cr;
					if (!empty($parameter->property)) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_property" value="'.addslashes($parameter->property).'"/>'.$cr;
					if ($parameter->deprecated) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_deprecated" value="'.addslashes($parameter->deprecated).'"/>'.$cr;
					if ($parameter->allow_wildcards) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_allow_wildcards" value="'.addslashes('yes').'"/>'.$cr;
					if (!empty($parameter->type)) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_type" value="'.addslashes($parameter->type).'"/>'.$cr;
					if (!empty($parameter->description)) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_description" value="'.addslashes($parameter->description).'"/>'.$cr;
					if ($parameter->required) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_required" value="'.addslashes($parameter->required).'"/>'.$cr;
					if (!empty($parameter->prompt)) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_prompt" value="'.addslashes($parameter->prompt).'"/>'.$cr;
					if (!empty($parameter->default)) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_default" value="'.addslashes($parameter->default).'"/>'.$cr;
					if (!empty($parameter->content_type)) $form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_content_type" value="'.addslashes($parameter->content_type).'"/>'.$cr;
					if (!empty($parameter->regex))$form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_regex" value="'.addslashes($parameter->regex).'"/>'.$cr;
					if (!empty($parameter->validation_method))$form .= $t.$t.$t.$t.'<input type="hidden" name="'.$name.'-help_message_validation_method" value="'.addslashes($parameter->validation_method).'"/>'.$cr;

					// Textarea Input
					if ($parameter->type == "textarea") {
						$form .= $t.$t.$t.$t.'<textarea class="value input apiInput apiTextArea" name="'.$name.'" onMouseOver="showAPIHelpMessage(this)" onMouseOut="hideAPIHelpMessage()">'.$default.'</textarea>'.$cr;
					}
					// Select Input
					elseif (count($parameter->options)) {
						$form .= $t.$t.$t.$t.'<select class="value input apiInput'.$required_class.'" name="'.$name.'" onMouseOver="showAPIHelpMessage(this)" onMouseOut="hideAPIHelpMessage()">';
						$options = $parameter->options;
						foreach ($options as $option) {
							$form .= $t.$t.$t.$t.$t.'<option value="'.$option.'">'.$option.'</option>'.$cr;
						}
						$form .= $t.$t.$t.$t.'</select>';
					}
					// Other Input Types
					else {
						if (!empty($parameter->prompt)) $form .= $t.$t.$t.$t.'<input type="'.$parameter->type.'" id="'.$name.'" name="'.$name.'" placeholder="'.$parameter->prompt.'" class="value input apiInput'.$required_class.'" value="'.$default.'" onMouseOver="showAPIHelpMessage(this)" onMouseOut="hideAPIHelpMessage()" />'.$cr;
						else $form .= $t.$t.$t.$t.'<input type="'.$parameter->type.'" id="'.$name.'" name="'.$name.'" class="value input apiInput'.$required_class.'" value="'.$default.'" onMouseOver="showAPIHelpMessage(this)" onMouseOut="hideAPIHelpMessage()" />'.$cr;
					}
					$form .= $t.$t.$t.'</div>'.$cr;
				}
				if ($method->show_controls) {
					$form .= $t.$t.$t.'<hr class="apiMethodControls"/>'.$cr;
					$form .= $t.$t.$t.'<div class="apiMethodControls">'.$cr;
					$form .= $t.$t.$t.$t.'<span class="label apiLabel">limit</span><input type="text" name="_limit" placeholder="Max Records" value="" class="value input"/>'.$cr;
					$form .= $t.$t.$t.$t.'<span class="label apiLabel">offset</span><input type="text" name="_offset" placeholder="First Record" value="" class="value input"/>'.$cr;
					$form .= $t.$t.$t.$t.'<span class="label apiLabel">sort</span><input type="text" name="_sort" value="" placeholder="Sort Field" class="value input"/>'.$cr;
					$form .= $t.$t.$t.$t.'<span class="label apiLabel">direction</span><input type="text" name="_direction" placeholder="ASC or DESC" value="" class="value input"/>'.$cr;
					$form .= $t.$t.$t.'</div>'.$cr;
				}
				$form .= $t.$t.$t.'<div class="apiMethodFooter"><input type="submit" name="btn_submit" value="Submit" class="button apiMethodSubmit"/></div>'.$cr;
				$form .= $t.$t.'</div>'.$cr;
				$form .= $t.'</form>'.$cr;
			}
			return $form;
		}

		/**
		 * Build Definition Document
		 */
		public function definition() {
			$api_name = "\\".ucfirst($this->module)."\\API";
			$api = new $api_name();
			$methods = $api->_methods();
			$definition_object = array();
			$components = array();
			$definition_object['openapi'] = "3.0.0";
			$definition_object['info'] = array(
				"title" => $this->module." API",
				"version" => $api->_version,
				"description" => $api->_release,
			);
			$definition_object['servers'] = array(
				array(
					"url" => "https://".$_SERVER['HTTP_HOST']."/api/".$api->_name,
				),
			);
			$definition_object['paths'] = array();
			foreach ($methods as $form_name => $settings) {
				if (!empty($settings['path'])) {
					if ($settings['return_type'] == 'int') {
						//Skip for now
					}
					else if (empty($settings['return_type'])) {
						//Skip for now
					}
					else {
						$class_name = "\\".str_replace('::','\\',$settings['return_type']);
						$class = new \ReflectionClass($class_name);
						$definition_object['paths'][$settings['path']] = array();
						if (!array_key_exists($settings['return_type'],$components)) {
							$properties = get_class_vars("\\".str_replace('::','\\',$settings['return_type']));
							unset($properties['_cached']);
							foreach ($properties as $key => $value) {
								$property = new \ReflectionProperty($class_name,$key);
								if ($property->hasType()) {
									$type = $property->getType();
									$typeString = $type->__toString();
									$typeString = str_replace('?','',$typeString);
									$properties[$key] = array(
										"type" => $typeString,
									);
								}
								else {
									$properties[$key] = array(
									);
								}
							}
							$components[$settings['return_type']] = array(
								"type" => "object",
								"properties" => $properties,
							);
						}
						if (empty($settings['verb'])) {
							if (preg_match('/^get/i',$form_name)) $settings['verb'] = 'get';
							else $settings['verb'] = 'post';
						}
						else ($settings['verb'] = strtolower($settings['verb']));
						if ($settings['verb'] == 'get') {
							$definition_object['paths'][$settings['path']]['get'] = array(
								"summary" => $settings['description'],
								"operationId" => $form_name,
								"parameters" => array(),
								"responses" => array(
									"200" => array(
										"description" => "Successful Operation",
										"content"	=> array(
											"application/xml" => array(
												"schema" => array(
													"type" => "array",
													"items" => array(
														"\$ref" => "#/components/schemas/".$settings['return_type'],
													),
												),
											),
										),
									),
								),
							);
						}
						elseif ($settings['verb'] == 'post') {
							$definition_object['paths'][$settings['path']]['post'] = array (
								"summary" => $settings['description'],
								"operationId" => $form_name,
								"requestBody" => array(
									"content" => array(
										"application/json" => array(
											"schema" => array(
												"type" => "object",
												"properties" => array(),
											),
										),
									),
								),
								"responses" => array(
									"200" => array(
										"description" => "Successful Operation",
									),
								),
							);
						}
					}
				}
			}
			$definition_object['components'] = array(
				"schemas" => $components,
			);
			header('Content-Type: text/plain');
			print yaml_emit($definition_object);
		}
	}
