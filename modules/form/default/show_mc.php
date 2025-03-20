<?php
###########################################
### /_form/show/<code>					###
### Display the form identified by		###
### <code> or with id post param.		###
###########################################

# Load Page Info
$site = new \Site();
$page = $site->page();
$can_proceed = true;

# Initialize form for validation
$form = new \Form\Form();

// Load Form based on parameters
$code = $_REQUEST['code'] ?? '';
$id = $_REQUEST['id'] ?? '';
$query_var = $GLOBALS['_REQUEST_']->query_vars_array[0] ?? null;

if (!empty($code)) {
	if (!$form->validText($code)) {
		$page->addError("Invalid form code format");
		$can_proceed = false;
	} else {
		$form->get($code);
		if (!$form->exists()) {
			$page->addError("Form not found!");
			$can_proceed = false;
		}
	}
} elseif (!empty($id)) {
	if (!$form->validInteger($id)) {
		$page->addError("Invalid form ID format");
		$can_proceed = false;
	} else {
		$form = new \Form\Form($id);
		if (!$form->exists()) {
			$page->addError("Form not found!");
			$can_proceed = false;
		}
	}
} elseif (!empty($query_var)) {
	if (!$form->validText($query_var)) {
		$page->addError("Invalid form code format");
		$can_proceed = false;
	} else {
		$form->get($query_var);
		if (!$form->exists()) {
			$page->addError("Form not found!");
			$can_proceed = false;
		}
	}
} else {
	$page->addError("No form identifier provided");
	$can_proceed = false;
}

// Load questions if form exists
$questions = array();
if ($can_proceed && $form->exists()) {
	$questions = $form->questions();
	if ($form->error()) {
		$page->addError($form->error());
		$can_proceed = false;
	}
}

// Handle Input
$submit = $_REQUEST['submit'] ?? null;
if (!empty($submit) && $can_proceed) {

	$type_values = $_REQUEST['type'] ?? array();
	$question_values = $_REQUEST['question'] ?? array();
	$prompt_values = $_REQUEST['prompt'] ?? array();
	$required_values = $_REQUEST['required'] ?? array();
	
	// Validate and update existing questions
	foreach ($questions as $question) {

		$type = $type_values[$question->id] ?? '';
		if (!$question->validType($type)) {
			$page->addError("Invalid question type for question ID " . $question->id);
			continue;
		}
		
		$question_text = $question_values[$question->id] ?? '';
		if (empty($question_text)) {
			$page->addError("Question text is required for question ID " . $question->id);
			continue;
		} elseif (!$question->validText($question_text)) {
			$page->addError("Invalid question text format");
			continue;
		}
		$prompt = $prompt_values[$question->id] ?? '';
		if (empty($prompt)) {
			$page->addError("Question prompt is required for question ID " . $question->id);
			continue;
		} elseif (!$question->validText($prompt)) {
			$page->addError("Invalid question prompt format");
			continue;
		}

		$required = $required_values[$question->id] ?? 0;
		if (empty($required)) {
			$page->addError("Question required is required for question ID " . $question->id);
			continue;
		} elseif (!$question->validInteger($required)) {
			$page->addError("Invalid question required format");
			continue;
		}
		
		$question->type = $type;
		$question->question = $question_text;
		$question->prompt = $prompt;
		$question->required = $required;
		$question->save();
	}

	// Add new question if provided
	$question_new = $_REQUEST['question_new'] ?? '';
	if (!empty($question_new)) {
		$question = new \Form\Question();
		$type_new = $_REQUEST['type_new'] ?? '';
		
		if (!$question->validType($type_new)) {
			$page->addError("Invalid new question type");
		} else {

			$prompt_new = $_REQUEST['prompt_new'] ?? '';
			if (empty($prompt_new)) {
				$page->addError("Question prompt is required");
				$can_proceed = false;
			} elseif (!$question->validText($prompt_new)) {
				$page->addError("Invalid question prompt format");
				$can_proceed = false;
			}

			$required_new = $_REQUEST['required_new'] ?? 0;
			if (empty($required_new)) {
				$page->addError("Question required is required");
				$can_proceed = false;
			} elseif (!$question->validInteger($required_new)) {
				$page->addError("Invalid question required format");
				$can_proceed = false;
			}

			if ($can_proceed) {
				$question->form_id = $form->id;
				$question->type = $type_new;
				$question->question = $question_new;
				$question->prompt = $prompt_new;
				$question->required = $required_new;
				$question->save();
			
				if ($question->error()) {
					$page->addError("Error adding new question: " . $question->error());
				} else {
					$page->appendSuccess("Question added successfully");
				}
			}
		}
	}

	// Reload Questions
	$questions = $form->questions();
	if ($form->error()) {
		$page->addError($form->error());
	}
}
