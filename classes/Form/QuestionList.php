<?php
	namespace Form;

	class QuestionList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Form\Question';
		}
	}
