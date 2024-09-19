<?php
	namespace Form;

	class Question Extends \BaseModel {
		public $form_id;
		public $type;
		public $text;
		public $question;
		public $prompt;
		public $required;

		public function __construct($id = 0) {
			$this->_tableName = 'form_questions';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
        }

		public function validType($type): bool {
			if (preg_match('/^(text|textarea|select|radio|checkbox|hidden)$/i',$type)) return true;
			return false;
		}
	}
