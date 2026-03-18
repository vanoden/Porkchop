<?php
	/** @class Form\VersionList
	 * Represents a list of versions of a form.  A form can have multiple versions, but only one active version at a time.
	 */
	namespace Form;

	class VersionList Extends \BaseListClass {
		public function __construct() {
			$this->_tableName = 'form_versions';
			$this->_modelName = 'Form\Version';
		}

		public function nextVersionNumber($form_id) {
			$versions = $this->find(array('form_id' => $form_id), array('date_activated' => 'DESC'));
			if (count($versions) == 0) return 1;
			return intval(substr($versions[0]->code, -3)) + 1;
		}
	}