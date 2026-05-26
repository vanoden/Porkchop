<?php
	namespace Form;

	class SubmissionList extends \BaseListClass {
		public function __construct() {
			$this->_tableName = 'form_submissions';
			$this->_modelName = '\Form\Submission';
		}
	}
