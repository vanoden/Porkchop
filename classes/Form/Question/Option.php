<?php
	/** @class Form\Question\Option
	 * Represents an option for a question.  This is used for select, radio, and checkbox questions.  Each option has a question_id, value, and label.
	 */
	namespace Form\Question;

	class Option Extends \BaseModel {
		public $question_id;	// ID of the question this option belongs to
		public $text;			// Text of the option, used for display purposes
		public $value;			// Value of the option, used for form submission
		public $sort_order;		// Display order for the option

		public function __construct($id = null) {
			$this->_tableName = 'form_question_options';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
			$this->_fields();
		}
	}
