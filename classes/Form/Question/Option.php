<?php
	namespace Form\Question;

	class Option Extends \BaseModel {
		public function __construct($id = null) {
			$this->_tableName = 'form_question_options';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
        }
	}
