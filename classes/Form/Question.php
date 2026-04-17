<?php
	namespace Form;

	class Question Extends \BaseModel {
		public $version_id;			// ID of the version this question belongs to
		public $aggregate_key;		// Stable key across versions for reporting/aggregation
		public $type;				// Type of the question, e.g., text, textarea, select, radio, checkbox, hidden
		public $text;				// Text of the question, used for display purposes
		public $prompt;				// Prompt for the question, used for display purposes
		public $example;			// Example answer for the question, used for display purposes
		public $validation_pattern;	// Regular expression pattern to validate the answer, if applicable
		public $group_id;			// Group ID for the question, used to group questions together for display purposes
		public $default;			// Default answer for the question, used for display purposes
		public $sort_order;			// Display order for the question
		public $required;			// Whether the question is required
		public $help;				// Special instructions for the question as needed, used for display purposes

		/** @method public __construct(id)
		 * Constructor for the Question class.  If an ID is provided, loads the question with that ID from the database.  Otherwise, creates a new question object.
		 * @param int $id The ID of the question to load, or null to create a new question object
		 * @return void
		 */
		public function __construct($id = 0) {
			$this->_tableName = 'form_questions';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
			$this->_fields();
		}

		public function add($parameters = array()) {
			if (empty($parameters['aggregate_key'])) {
				$pc = new \Porkchop();
				$parameters['aggregate_key'] = substr(str_replace(array('-', '_'), '', $pc->biguuid()), 0, 32);
			}
			if (empty($parameters['prompt']) && ! empty($parameters['text'])) {
				$parameters['prompt'] = $parameters['text'];
			}
			return parent::add($parameters);
		}

		/** @method public validType(type): bool
		 * Validates the type of the question. Returns true if the type is valid, false otherwise.
		 * @param string $type The type of the question to validate
		 * @return bool True if the type is valid, false otherwise
		 */
		public function validType($type): bool {
			if (preg_match('/^(text|textarea|select|radio|checkbox|hidden|submit)$/i', $type)) {
				return true;
			}
			return false;
		}

		/** @method public options(): array
		 * Retrieves the options for the question. Returns an array of options.
		 * @return array An array of options for the question
		 */
		public function options() {
			$optionList = new \Form\Question\OptionList();
			return $optionList->find(array(
				'question_id' => $this->id,
				'_sort' => 'sort_order',
				'_order' => 'ASC',
			));
		}

		public function validName($string): bool {
			return $this->validText($string) && strlen(trim($string)) > 0;
		}

		public function addOption(array $parameters): ?\Form\Question\Option {
			$parameters['question_id'] = $this->id;
			$o = new \Form\Question\Option();
			if (! $o->add($parameters)) {
				$this->error($o->error());
				return null;
			}
			return $o;
		}

		public function dropOptions(): bool {
			foreach ($this->options() as $opt) {
				if (! $opt->drop()) {
					$this->error($opt->error());
					return false;
				}
			}
			return true;
		}
	}
