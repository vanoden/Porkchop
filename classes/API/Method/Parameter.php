<?php
	namespace API\Method;

	class Parameter Extends \BaseClass {
		public $name;
		public $description = '';
		public $type = 'text';
		public $required = false;
		public $options = [];
		public $default = null;
		public $format = null;

		public function __construct($parameters = []) {
			foreach ($parameters as $name => $value) {
				if ($name == "description") $this->description = $value;
				elseif ($name == "name") $this->name = $value;
				elseif ($name == "type")
					if ($this->validType($value))
						$this->type = $value;
					else {
						$this->error("Invalid Type");
					}
				elseif ($name == "required")
					if ($value == "false") $this->required = false;
					else $this->required = true;
				elseif ($name == "options") {
					foreach ($value as $option) {
						array_push($this->options,$option);
					}
				}
				elseif ($name == "default") $this->default = $value;
				elseif ($name == "format") $this->format = $value;
			}
		}
	}
