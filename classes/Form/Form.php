<?php
	namespace Form;

	class Form Extends \BaseModel {
		public function __construct($id = null) {
			$this->_tableName = 'form_forms';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
        }

		public function questions() {
			$questionList = new \Form\QuestionList();
			return $questionList->find(array('form_id' => $this->id));
		}
	}
