<?php
	namespace API;

	class Method Extends \BaseClass {
		public $description = '';
		public $authentication_required = true;
		public $privilege_required = null;
		private $_parameters = [];

		public function __construct($parameters = []) {
			// See if definition is old version or new
			if (isset($parameters['parameters'])) {
				// New definition
				if (isset($parameters['description'])) $this->description = $parameters['description'];
				if (isset($parameters['authentication_required'])) {
					if ($parameters['authentication_required'] == 'false' || $parameters['authentication_required'] == false) $this->authentication_required = false;
				}
				else $this->authentication_required = true;
				if (isset($parameters['privilege_required'])) $this->privilege_required = $parameters['privilege_required'];

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
