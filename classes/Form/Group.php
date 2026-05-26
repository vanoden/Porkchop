<?php
	namespace Form;

	class Group Extends \BaseModel {
		public $version_id;
		public $title;
		public $instructions;
		public $sort_order;

		public function __construct($id = null) {
			$this->_tableName = 'form_question_groups';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
			$this->_fields();
		}
	}
