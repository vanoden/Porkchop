<?php
	namespace Form;

	/** One submitted instance of a form (a set of answers at a point in time). */
	class Submission extends \BaseModel {
		public $form_id;
		public $version_id;
		public $date_submitted;
		public $object_type;
		public $object_id;
		public $remote_addr;

		public function __construct($id = 0) {
			$this->_tableName = 'form_submissions';
			$this->_cacheKeyPrefix = $this->_tableName;
			parent::__construct($id);
		}

		/**
		 * @param array $answers question_id => scalar or array for checkbox
		 */
		public function recordAnswers(array $answers): bool {
			foreach ($answers as $question_id => $value) {
				$q = new Question((int)$question_id);
				if (! $q->exists()) {
					$this->error('Invalid question');
					return false;
				}
				if ((int)$q->version_id !== (int)$this->version_id) {
					$this->error('Question does not match submission version');
					return false;
				}
				if (is_array($value)) {
					$stored = json_encode(array_values($value));
				} else {
					$stored = (string)$value;
				}
				$ans = new Submission\Answer();
				if (! $ans->add(array(
					'submission_id' => $this->id,
					'question_id' => $q->id,
					'aggregate_key' => $q->aggregate_key,
					'value' => $stored,
				))) {
					$this->error($ans->error());
					return false;
				}
			}
			return true;
		}
	}
