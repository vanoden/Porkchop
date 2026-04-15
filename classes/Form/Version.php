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
			$this->_fields();
		}

		public function questions() {
			$questionList = new \Form\QuestionList();
			return $questionList->find(array('version_id' => $this->id));
		}

		public function active(): bool {
			$form = new \Form\Form($this->form_id);
			if (empty($form->active_version_id)) {
				return false;
			}
			return (int)$form->active_version_id === (int)$this->id;
		}

		/** Mark this version as the published one for the form. */
		public function publish(?int $user_id = null): bool {
			$form = $this->form();
			$params = array(
				'user_id_activated' => $user_id,
				'date_activated' => date('Y-m-d H:i:s'),
			);
			if (! $this->update($params)) {
				return false;
			}
			return $form->setActiveVersion($this);
		}

		public function form(): \Form\Form {
			return new \Form\Form($this->form_id);
		}

		/**
		 * Copy all questions/options from a source version into this version.
		 * Preserves aggregate_key so reporting can aggregate the same question across versions.
		 */
		public function copyQuestionsFrom(\Form\Version $source): bool {
			if (! $this->exists() || ! $source->exists()) {
				$this->error('Source or destination version not found');
				return false;
			}
			if ((int)$this->form_id !== (int)$source->form_id) {
				$this->error('Versions must belong to the same form');
				return false;
			}
			foreach ($source->questions() as $q) {
				$nq = new \Form\Question();
				$params = array(
					'version_id' => $this->id,
					'type' => $q->type,
					'text' => $q->text,
					'prompt' => $q->prompt,
					'example' => $q->example,
					'validation_pattern' => $q->validation_pattern,
					'group_id' => $q->group_id,
					'default' => $q->default,
					'sort_order' => $q->sort_order,
					'required' => $q->required,
					'help' => $q->help,
					'aggregate_key' => $q->aggregate_key,
				);
				if (! $nq->add($params)) {
					$this->error($nq->error() ?: 'Could not copy question');
					return false;
				}
				foreach ($q->options() as $o) {
					$no = new \Form\Question\Option();
					if (! $no->add(array(
						'question_id' => $nq->id,
						'text' => $o->text,
						'value' => $o->value,
						'sort_order' => $o->sort_order,
					))) {
						$this->error($no->error() ?: 'Could not copy option');
						return false;
					}
				}
			}
			return true;
		}

		/** Human-readable name for {@see $user_id_activated}, or `User #id` if the account is missing. */
		public function activatedByDisplayName(): string {
			if (empty($this->user_id_activated)) {
				return '';
			}
			$uid = (int)$this->user_id_activated;
			$customer = new \Register\Customer($uid);
			if ($customer->exists()) {
				return $customer->full_name();
			}
			return 'User #'.$uid;
		}
	}