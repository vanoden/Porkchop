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

		/**
		 * Suggested label for “Add New Version”: "{title} - Version N".
		 * Infers N from legacy numeric names, "{title} - Version k", "{title} k", exact "{title}", or falls back to nextVersionNumber() if title is empty.
		 */
		public function nextDefaultVersionName(int $form_id, string $formTitle = ''): string {
			$title = trim($formTitle);
			if ($title === '') {
				return $this->nextVersionNumber($form_id);
			}
			$versions = $this->find(array('form_id' => $form_id));
			$max = 0;
			$escaped = preg_quote($title, '/');
			foreach ($versions as $v) {
				$name = trim((string)($v->name ?? ''));
				if ($name === '') {
					continue;
				}
				if (ctype_digit($name)) {
					$n = (int)$name;
					if ($n > $max) {
						$max = $n;
					}
					continue;
				}
				if ($name === $title) {
					$max = max($max, 1);
					continue;
				}
				if (preg_match('/^' . $escaped . '\s*-\s*Version\s+(\d+)$/u', $name, $m)) {
					$n = (int)$m[1];
					if ($n > $max) {
						$max = $n;
					}
					continue;
				}
				if (preg_match('/^' . $escaped . '\s+(\d+)$/u', $name, $m)) {
					$n = (int)$m[1];
					if ($n > $max) {
						$max = $n;
					}
				}
			}
			return $title . ' - Version ' . (string)($max + 1);
		}
	}