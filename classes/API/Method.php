<?php
	namespace API;

	class Method Extends \BaseClass {
		public $description = '';
		public $authentication_required = true;
		public $privilege_required = null;
		public $token_required = false;
		public $return_element;
		public $return_type;
		public $return_mime_type = 'application/xml';
		private $_parameters = [];

		public function __construct($parameters = []) {
			// See if definition is old version or new
			if (isset($parameters['parameters'])) {
				// New definition
				if (isset($parameters['description'])) $this->description = $parameters['description'];
				if (isset($parameters['authentication_required'])) {
					if (is_bool($parameters['authentication_required'])) $this->authentication_required = $parameters['authentication_required'];
					elseif ($parameters['authentication_required'] == 'false') $this->authentication_required = false;
				}
				if (isset($parameters['token_required'])) {
					if (is_bool($parameters['token_required'])) $this->token_required = $parameters['token_required'];
					elseif ($parameters['token_required'] == 'false') $this->token_required = false;
				}
				if (isset($parameters['privilege_required'])) $this->privilege_required = $parameters['privilege_required'];
				if (isset($parameters['return_element'])) $this->return_element = $parameters['return_element'];
				if (isset($parameters['return_type'])) $this->return_type = $parameters['return_type'];
				if (isset($parameters['return_mime_type'])) $this->return_mime_type = $parameters['return_mime_type'];

				$this->parameters($parameters['parameters']);
			}
			else {
				$this->description = '';
				$this->authentication_required = true;
				$this->privilege_required = null;
				$this->parameters($parameters);
			}
		}

		public function parameters($parameters = []) {
			foreach ($parameters as $name => $parameter) {
				$this->_parameters[$name] = new \API\Method\Parameter($parameter);
			}
			return $this->_parameters;
		}
	}
