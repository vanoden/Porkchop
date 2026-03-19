<?php
	/** @class Form\Question\OptionList
	 * Represents a list of options for a question.  This is used for select, radio, and checkbox questions.  Each option has a question_id, value, and label.
	 */
	namespace Form\Question;

	class OptionList Extends \BaseListClass {
		public function __construct() {
			$this->_modelName = '\Form\Question\Option';
		}
	}