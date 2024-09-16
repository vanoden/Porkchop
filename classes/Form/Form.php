<?php
	namespace Form;

	class Form Extends \BaseModel {
		public $code;
		public $title;
		public $description;
		public $instructions;
		public $action;
		public $method = 'post';

		public function __construct($id = null) {
			$this->_tableName = 'form_forms';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
        }

		public function questions() {
			$questionList = new \Form\QuestionList();
			return $questionList->find(array('form_id' => $this->id));
		}

		public function validMethod($string) {
			if (preg_match('/^(get|post)$/i',$string)) return true;
			return false;
		}

		public function validAction($url) {
			if (preg_match('/^https?:\/\/[a-z0-9\.\-]+\/[a-z0-9\.\-\/]+$/i',$url)) return true;
			if (preg_match('/^_(\w[\w\_]*)\/(\w[\w\_]*)$/i',$url)) return true;
			if (empty($url)) return true;
			return false;
		}
	}
