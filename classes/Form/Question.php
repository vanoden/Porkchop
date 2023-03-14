<?php
	namespace Form;

	class Question Extends \BaseModel {
		public function __construct($id = null) {
			$this->_tableName = 'form_questions';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
        }
	}
