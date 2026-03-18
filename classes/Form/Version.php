<?php
	/** @class Form\Version
	 * Represents a version of a form.  A form can have multiple versions, but only one active version at a time.
	 */
	namespace Form;

	class Version Extends \BaseModel {
		public $form_id;				// ID of form this version belongs to
		public $code;					// Unique code for this version, used to load specific versions of a form
		public $name;					// Name of this version, used for display purposes
		public $description;			// Description of this version, why was it created?
		public $instructions;			// Instructions for this version, used for display purposes
		public $user_id_activated;		// ID of user that activated this version
		public $date_activated;			// Date this version was activated, if a newer active one exists, this one is inactive

		public function __construct($id = null) {
			$this->_tableName = 'form_versions';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
		}

		public function questions() {
			$questionList = new \Form\QuestionList();
			return $questionList->find(array('version_id' => $this->id));
		}

		public function active() {
			$form = new \Form\Form($this->form_id);
			return $form->activeVersion()->id == $this->id;
		}

		public function form(): \Form\Form {
			return new \Form\Form($this->form_id);
		}
	}