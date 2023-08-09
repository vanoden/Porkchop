<?php
	namespace Site\Page;

	class SearchBar Extends \BaseModel {
		public $categories;
		public $previous_string;

		public function __construct() {
			$this->categories = array(
				"customer" => array(
					"uri"	=> "/_register/accounts",
					"inputName" => "search",
					"submitName" => "btn_search"
				),
				"product" => array(
					"uri"	=> "/_product/search",
					"inputName" => "search",
					"submitName" => "btn_search"
				),
				"support" => array(
					"uri"	=> "/_support/search",
					"inputName" => "search"
				),
				"engineering" => array(
					"uri"	=> "/_engineering/tasks",
					"inputName" => "search"
				),
			);
		}

		public function previousString($string) {
			if (!empty($string)) $this->previous_string = $string;
			return $this->previous_string;
		}

		public function formContent() {
			$string  = '<form method="get" action="'.$this->categories[$_REQUEST['category']]['uri'].'">';
			$string .= '<input type="text" name="'.$this->categories[$_REQUEST['category']]['inputName'].'" value="'.$this->previous_string.'" />';
			$string .= '<input type="submit" name="'.$this->categories[$_REQUEST['category']]['submitName'].'" value="Search" />';
			$string .= '</form>';
			return $string;
		}
	}