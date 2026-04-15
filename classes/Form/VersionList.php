<?php
	/** @class Form\VersionList
	 * Represents a list of versions of a form.  A form can have multiple versions, but only one active version at a time.
	 */
	namespace Form;

	class VersionList Extends \BaseListClass {
		public function __construct() {
			$this->_tableName = 'form_versions';
			// Must be fully qualified: in namespace Form, 'Form\Version' wrongly resolves to Form\Form\Version.
			$this->_modelName = '\Form\Version';
		}

		/** Next default version name: "1", "2", … based on numeric names, or count+1 if none are numeric. */
		public function nextVersionNumber($form_id) {
			$versions = $this->find(array('form_id' => (int)$form_id));
			$max = 0;
			foreach ($versions as $v) {
				$name = trim((string)($v->name ?? ''));
				if ($name !== '' && ctype_digit($name)) {
					$n = (int)$name;
					if ($n > $max) {
						$max = $n;
					}
				}
			}
			if ($max > 0) {
				return (string)($max + 1);
			}
			return (string)max(1, count($versions) + 1);
		}
	}