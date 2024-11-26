<?php
	namespace API\Method;

	class Parameter Extends \BaseClass {
		public $name;
		public $object;
		public $property;
		public $description = '';
		public $type = 'text';
		public $required = false;
		public $requirement_group = null;
		public $prompt = '';
		public $options = [];
		public $default = null;
		public $format = null;
		public $parameter_type = 'string';
		public $validation_method = null;
		public $regex = null;
		public $hidden = false;
		public $deprecated = false;
		public $allow_wildcards = false;
		public $show_controls = false;
		public $content_type = null;

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
					if (is_bool($value)) $this->required = $value;
					elseif ($value == "false") $this->required = false;
					else $this->required = true;
				elseif ($name == "requirement_group") $this->requirement_group = $value;
				elseif ($name == "options") {
					foreach ($value as $option) {
						array_push($this->options,$option);
					}
				}
				elseif ($name == "object") $this->object = $value;
				elseif ($name == "property") $this->property = $value;
				elseif ($name == "prompt") $this->prompt = $value;
				elseif ($name == "default") $this->default = $value;
				elseif ($name == "format") $this->format = $value;
				elseif ($name == "parameter_type") $this->parameter_type = $value;
				elseif ($name == "regex") $this->regex = $value;
				elseif ($name == "validation_method") $this->validation_method = $value;
				elseif ($name == "hidden") 
					if (is_bool($value)) $this->hidden = $value;
					elseif ($value == "false") $this->hidden = false;
					else $this->hidden = true;
				elseif ($name == "deprecated")
					if (is_bool($value)) $this->deprecated = $value;
					elseif ($value == "false") $this->deprecated = false;
					else $this->deprecated = true;
				elseif ($name == "allow_wildcards") {
					if (is_bool($value)) $this->allow_wildcards = $value;
					elseif ($value == "false") $this->allow_wildcards = false;
					else $this->allow_wildcards = true;
				}
				elseif ($name == "content-type") {
					switch($value) {
						case "int":
							$this->content_type = "int";
							break;
						case "string":
							$this->content_type = "string";
							break;
						case "boolean":
							$this->content_type = "boolean";
							break;
						case "bool":
							$this->content_type = "boolean";
							break;
						case "float":
							$this->content_type = "float";
							break;
						default:
							$this->content_type = "text";
							break;
					}
				}
				else {
					$this->error("Invalid Parameter: ".$name);
				}
			}
		}
	}
