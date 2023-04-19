<?php
	###########################################
	### /_form/edit/<code>					###
	### Allow add/edit/drop of questions	###
	### for the form specified with <code>	###
	### or the post param id.				###
	###########################################

	# Load Page
	$site = new \Site();
	$page = $site->page();
	$porkchop = new \Porkchop();

	# Load Form
	if (!empty($_REQUEST['code'])) {
		$form = new \Form\Form();
		$form->get($_REQUEST['code']);
		if (! $form->exists()) $page->addError("Form not found!");
	}
	elseif (!empty($_REQUEST['id'])) {
		$form = new \Form\Form($_REQUEST['id']);
		if (! $form->exists()) $page->addError("Form not found!");
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$form = new \Form\Form();
		$form->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
		if ($form->error()) $page->addError($form->error());
	}
	else {
		$form = new \Form\Form();
	}

	if (!empty($_REQUEST['submit'])) {
		if (!$form->exists()) {
			if (empty($_REQUEST['code'])) $_REQUEST['code'] = $porkchop->uuid();
			if (!$form->validMethod($_REQUEST['method'])) $_REQUEST['method'] = 'POST';
			if (!$form->validAction($_REQUEST['action'])) $_REQUEST['action'] = '';

			$parameters = array(
				'code'	=> $_REQUEST['code'],
				'title'	=> $_REQUEST['title'],
				'action'	=> $_REQUEST['action'],
				'method'	=> $_REQUEST['method'],
				'description'	=> $_REQUEST['description'],
				'instructions'	=> $_REQUEST['instructions'],
			);
			if (! $form->add($parameters)) {
				$page->addError("Error adding form: " . $form->error());
			}
			else {
				$page->appendSuccess("Form added.");
			}
		}
		else {
			$parameters = array(
				'code'	=> $_REQUEST['code'],
				'title'	=> $_REQUEST['title'],
				'active'	=> $_REQUEST['active'],
				'method'	=> $_REQUEST['method'],
				'description'	=> $_REQUEST['description'],
				'instructions'	=> $_REQUEST['instructions'],
			);
			if (! $form->update($parameters)) {
				$page->addError("Error updating form: " . $form->error());
			}
			else {
				$page->appendSuccess("Form updated.");
			}
		}

		foreach ($_REQUEST['answer'] as $question_id => $answer) {
			$question = new \Form\Question($question_id);
			if ($question->validType($_REQUEST['type'][$question_id])) {
				$page->addError("Invalid question type: " . $_REQUEST['type'][$question_id]);
			}
			else {
				$parameters = array(
					'type'	=> $_REQUEST['type'][$question_id],
					'text'	=> $_REQUEST['text'][$question_id],
					'prompt'	=> $_REQUEST['prompt'][$question_id],
					'required'	=> $_REQUEST['required'][$question_id],
				);

				if (! $question->update($parameters)) {
					$page->addError("Error updating question: " . $question->error());
				}
				else {
					$page->appendSuccess("Question updated.");
				}
			}
		}

		if (!empty($_REQUEST['type_new'])) {
			$question = new \Form\Question();
			$_REQUEST['text_new'] = trim(noXSS($_REQUEST['text_new']));

			if (!$question->validType($_REQUEST['type_new'])) {
				$page->addError("Invalid question type: " . $_REQUEST['type_new']);
			}
			elseif (empty($_REQUEST['text_new'])) {
				$page->addError("Question text is required.");
			}
			else {
				$parameters = array(
					'form_id'	=> $form->id,
					'type'	=> $_REQUEST['type_new'],
					'text'	=> $_REQUEST['text_new'],
					'prompt'	=> $_REQUEST['prompt_new'],
					'required'	=> $_REQUEST['required_new'],
				);

				if (! $question->add($parameters)) {
					$page->addError("Error adding question: " . $question->error());
				}
				else {
					$page->appendSuccess("Question added.");
				}

			}
		}
	}

	// Load Questions
	$questions = $form->questions();
	if ($form->error()) $page->addError($form->error());

	$page->title("Edit Form");
	$page->addBreadcrumb("Forms","/_form/browse");