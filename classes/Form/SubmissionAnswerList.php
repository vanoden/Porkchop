<?php
	namespace Form;

	class SubmissionAnswerList extends \BaseListClass {
		public function __construct() {
			$this->_tableName = 'form_submission_answers';
			$this->_modelName = '\Form\Submission\Answer';
		}
	}
