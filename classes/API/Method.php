<?php
	namespace API;

	class Method Extends \BaseClass {
		public $description = '';
		public $verb = 'GET';
		public $path = '';
		public $deprecated = false;
		public $hidden = false;
		public $authentication_required = true;
		public $privilege_required = null;
		public $token_required = false;
		public $return_element;
		public $return_type;
		public $return_mime_type = 'application/xml';
		public $show_controls = false;
		private $_parameters = [];

		public function __construct($parameters = []) {
			// See if definition is old version or new
			if (isset($parameters['parameters'])) {
				// New definition
				if (isset($parameters['path'])) $this->path = $parameters['path'];
				if (isset($parameters['verb'])) $this->verb = $parameters['verb'];
				if (isset($parameters['description'])) $this->description = $parameters['description'];
				if (isset($parameters['authentication_required'])) {
					if (is_bool($parameters['authentication_required'])) $this->authentication_required = $parameters['authentication_required'];
					elseif ($parameters['authentication_required'] == 'false') $this->authentication_required = false;
				}
				if (isset($parameters['token_required'])) {
					if (is_bool($parameters['token_required'])) $this->token_required = $parameters['token_required'];
					elseif ($parameters['token_required'] == 'false') $this->token_required = false;
				}
				if (isset($parameters['privilege_required'])) {
					$this->privilege_required = $parameters['privilege_required'];
					if (!empty($parameters['privilege_required'])) $this->authentication_required = true;
				}
				if (isset($parameters['return_element'])) $this->return_element = $parameters['return_element'];
				if (isset($parameters['return_type'])) $this->return_type = $parameters['return_type'];
				if (isset($parameters['return_mime_type'])) $this->return_mime_type = $parameters['return_mime_type'];
				if (isset($parameters['show_controls'])) {
					if (is_bool($parameters['show_controls'])) $this->show_controls = $parameters['show_controls'];
					elseif ($parameters['show_controls'] == 'true') $this->show_controls = true;
				}
				if (isset($parameters['deprecated'])) {
					if (is_bool($parameters['deprecated'])) $this->deprecated = $parameters['deprecated'];
					elseif ($parameters['deprecated'] == 'true') $this->deprecated = true;
				}
				if (isset($parameters['hidden'])) {
					if (is_bool($parameters['hidden'])) $this->hidden = $parameters['hidden'];
					elseif ($parameters['hidden'] == 'true') $this->hidden = true;
				}

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
