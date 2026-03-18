<?php
	/** @class Form\Form
	 * Represents a form, which can have multiple versions.  Only one version of a form can be active at a time.  A form has a code, title, description, instructions, action, and method.  The code is used to load the form, and must be unique.
	 */
	namespace Form;

	class Form Extends \BaseModel {
		public $code;				// Unique code for this form, used to load specific forms
		public $title;				// Title of the form, used for display purposes
		public $instructions;		// Instructions for the form, used for display purposes
		public $description;		// Description of the form, used for display purposes
		public $action;				// Action URL for the form, where the form will be submitted
		public $method = 'post';	// Method for the form, either 'get' or 'post'

		public function __construct($id = null) {
			$this->_tableName = 'form_forms';
			$this->_cacheKeyPrefix = $this->_tableName;

			parent::__construct($id);
        }

		public function validMethod($string) {
			if (preg_match('/^(get|post)$/i',$string)) return true;
			return false;
		}

		public function validAction($url) {
			if (preg_match('/^https?:\/\/[a-z0-9\.\-]+\/[a-z0-9\.\-\/]+$/i',$url)) return true;
			if (preg_match('/^_(\w[\w\_]*)\/(\w[\w\_]*)$/i',$url)) return true;
			if (empty($url)) return true;
			return false;
		}

		public function render(): void {
			$activeVersion = $this->activeVersion();
			$questions = $activeVersion->questions();
			print '<div class="form_instructions">'.$activeVersion->instructions.'</div>\n';
			print '<form action="'.$this->action.'" method="'.$this->method.'">';
			foreach ($questions as $question) {
				print '<div class="formQuestion">';
				print '<label>'.$question->text.'</label>';
				if (!empty($question->instructions)) {
					print '<div class="formQuestionInstructions">'.$question->instructions.'</div>';
				}
				if ($question->type == 'text') {
					print '<input type="text" name="question_'.$question->id.'"';
					if ($question->required) print ' required';
					print '>';
				}
				elseif ($question->type == 'textarea') {
					print '<textarea name="question_'.$question->id.'"';
					if ($question->required) print ' required';
					print '></textarea>';
				}
				elseif ($question->type == 'select') {
					print '<select name="question_'.$question->id.'"';
					if ($question->required) print ' required';
					print '>';
					foreach ($question->options() as $option) {
						print '<option value="'.$option->value.'">'.$option->label.'</option>';
					}
					print '</select>';
				}
				elseif ($question->type == 'radio') {
					foreach ($question->options() as $option) {
						print '<label><input type="radio" name="question_'.$question->id.'" value="'.$option->value.'"';
						if ($question->required) print ' required';
						print '>'.$option->label.'</label>';
					}
				}
				elseif ($question->type == 'checkbox') {
					foreach ($question->options() as $option) {
						print '<label><input type="checkbox" name="question_'.$question->id.'[]" value="'.$option->value.'"';
						if ($question->required) print ' required';
						print '>'.$option->label.'</label>';
					}
				}
				elseif ($question->type == 'hidden') {
					print '<input type="hidden" name="question_'.$question->id.'" value="'.$question->text.'">';
				}
				print '</div>';
			}
			print '</form>';
		}
	}
