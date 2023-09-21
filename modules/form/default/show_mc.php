<?php
###########################################
### /_form/show/<code>					###
### Display the form identified by		###
### <code> or with id post param.		###
###########################################

# Load Page Info
$site = new \Site();
$page = $site->page();

	// Load Form
	if (!empty($_REQUEST['code'])) {
		$form = new \Form\Form();
		$form->get($_REQUEST['code']);
	}
	elseif (!empty($_REQUEST['id'])) {
		$form = new \Form\Form($_REQUEST['id']);
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$form = new \Form\Form();
		$form->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}

	if (! $form->exists()) {
		$page->addError("Form not found!");
	}
	else {
		# Load Questions
		$questions = $form->questions();
	}

	// Handle Input
	if (!empty($_REQUEST['submit'])) {
		// Update Questions
		foreach($questions as $question) {
			$question->type = $_REQUEST['type'][$question->id];
			$question->question = $_REQUEST['question'][$question->id];
			$question->prompt = $_REQUEST['prompt'][$question->id];
			$question->required = $_REQUEST['required'][$question->id];
			$question->save();
		}

		// Add New Question
		if (!empty($_REQUEST['question_new'])) {
			$question = new \Form\Question();
			$question->form_id = $form->id;
			$question->type = $_REQUEST['type_new'];
			$question->question = $_REQUEST['question_new'];
			$question->prompt = $_REQUEST['prompt_new'];
			$question->required = $_REQUEST['required_new'];
			$question->save();
		}

		// Reload Questions
		$questions = $form->questions();
	}
