<?php
	/** @view /_form/edit
	 * View for editing a form.  Displays a form with
	 * fields for the form code, title, description,
	 * instructions, and questions.  Provides a link
	 * to save the form and add new questions.
	 */
	// Load Page
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();
	
	// Initialize form for validation
	$form = new \Form\Form();

	// Load Form based on parameters
	if ($_REQUEST['id'] ?? false) {
		$version = new \Form\Version($_REQUEST['id']);
		if (!$version->exists()) {
			$page->addError("Form version not found!");
			$can_proceed = false;
		}
		else {
			$form = new \Form\Form($version->form_id);
			if (!$form->exists()) {
				$page->addError("Form not found for this version!");
				$can_proceed = false;
			}
		}
	}
	elseif ($_REQUEST['form_id']) {
		$form = new \Form\Form($_REQUEST['form_id']);
		if (!$form->exists()) {
			$page->addError("Form not found!");
			$can_proceed = false;
		}
		else {
			$version = new \Form\Version();
			$version->form_id = $form->id;
		}
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$form = new \Form\Form();
		if (! $form->get($GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$page->addError("Form not found!");
		}
		if (!empty($GLOBALS['_REQUEST_']->query_vars_array[1])) {
			$version = new \Form\Version();
			if (!$version->get($GLOBALS['_REQUEST_']->query_vars_array[1]) || $version->form_id != $form->id) {
				$page->addError("Form version not found!");
			}
		}
		else {
			$versionList = new \Form\VersionList();
			$version = new \Form\Version();
			$version->form_id = $form->id;
			$version->name = $versionList->nextVersionNumber($form->id);
		}
	}
	if (!$page->errorCount() && !isset($version)) {
		$form->code = $porkchop->biguuid();
	}

	if (!empty($_POST) && !$page->errorCount()) {
		// Validate Parameters
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
			$page->addError("Invalid Request, please reload the form and try again.");
		}

		if (empty($_REQUEST['code'])) {
			$_REQUEST['code'] = $porkchop->biguuid();
		}
		elseif (!$form->validCode($_REQUEST['code'])) {
			$page->addError("Invalid or missing form code");
		}

		if (empty($_REQUEST['name'])) {
			$page->addError("Version name is required");
		}
		elseif (!$form->validName($_REQUEST['name'])) {
			$page->addError("Invalid form name format");
		}

		$method = $_REQUEST['method'] ?? 'POST';
		if (!$form->validMethod($method)) {
			$page->addError("Invalid method format");
		}

		$action = $_REQUEST['action'] ?? '';
		if (!empty($action) && !$form->validAction($action)) {
			$page->addError("Invalid action format");
		}

		if (!empty($_REQUEST['description']) && !$form->validText($_REQUEST['description'])) {
			$page->addError("Invalid description format");
		}

		if (!empty($_REQUEST['instructions']) && !$form->validText($_REQUEST['instructions'])) {
			$page->addError("Invalid instructions format");
		}

		$parameters = array(
			'code' => $_POST['code'] ?? '',
			'name' => $_POST['name'] ?? '',
			'action' => $_POST['action'] ?? '',
			'method' => $_POST['method'] ?? '',
			'description' => $_POST['description'] ?? '',
			'instructions' => $_POST['instructions'] ?? '',
		);
		if (! $page->errorCount()) {
			if (!$version->exists()) {
				if (!$version->add($parameters)) {
					$page->addError("Error adding version: " . $version->error());
				} else {
					$page->appendSuccess("Version added.");
				}
			}
			else {
				// Update existing form
				if (!$form->update($parameters)) {
					$page->addError("Error updating form: " . $form->error());
					$can_proceed = false;
				} else {
					$page->appendSuccess("Form updated.");
				}
			}
		}

		// Process questions if form was successfully created/updated
		if ($can_proceed) {
			$answers = $_REQUEST['answer'] ?? array();
			foreach ($answers as $question_id => $answer) {
				$question = new \Form\Question($question_id);
				$question_type = $_REQUEST['type'][$question_id] ?? '';
				
				if (!$question->validType($question_type)) {
					$page->addError("Invalid question type: " . $question_type);
				} else {
					
					$question_text = $_REQUEST['text'][$question_id] ?? '';
					if (empty($question_text)) {
						$page->addError("Question text is required");
						$can_proceed = false;
					} elseif (!$question->validText($question_text)) {
						$page->addError("Invalid question text format");
						$can_proceed = false;
					}
					$question_prompt = $_REQUEST['prompt'][$question_id] ?? '';
					if (empty($question_prompt)) {
						$page->addError("Question prompt is required");
						$can_proceed = false;
					} elseif (!$question->validText($question_prompt)) {
						$page->addError("Invalid question prompt format");
						$can_proceed = false;
					}
					$question_required = $_REQUEST['required'][$question_id] ?? 0;
					if (empty($question_required)) {
						$page->addError("Question required is required");
						$can_proceed = false;
					} elseif (!$question->validInteger($question_required)) {
						$page->addError("Invalid question required format");
						$can_proceed = false;
					}

					if ($can_proceed) {
						$parameters = array(
							'type' => $question_type,
							'text' => $question_text,
							'prompt' => $question_prompt,
							'required' => $question_required,
						);

						if (!$question->update($parameters)) {
							$page->addError("Error updating question: " . $question->error());
						} else {
								$page->appendSuccess("Question updated.");
						}
					}
				}
			}

			// Add new question if provided
			if (!empty($_REQUEST['text_new'])) {
				$question = new \Form\Question();
				$text_new = trim(noXSS($_REQUEST['text_new'] ?? ''));

				if (!$question->validType($_REQUEST['type_new'] ?? '')) {
					$page->addError("Invalid question type: " . $_REQUEST['type_new']);
				} elseif (empty($_REQUEST['text_new'])) {
					$page->addError("Question text is required.");
				} else {
					if (empty($_REQUEST['prompt_new'])) {
						$page->addError("Question prompt is required");
					} elseif (!$question->validText($_REQUEST['prompt_new'] ?? '')) {
						$page->addError("Invalid question prompt format");
					}

					if (empty($_REQUEST['required_new'])) {
						$_REQUEST['required_new'] = 0;
					}	
					elseif (!$question->validInteger($_REQUEST['required_new'])) {
						$page->addError("Invalid question required format");
						$can_proceed = false;
					}
					if (!$page->errorCount()) {
						$parameters = array(
							'form_id' => $form->id,
							'type' => $_REQUEST['type_new'] ?? '',
							'text' => $_REQUEST['text_new'] ?? '',
							'prompt' => $_REQUEST['prompt_new'] ?? '',
							'required' => $_REQUEST['required_new'] ?? 0,
						);

						if (!$question->add($parameters)) {
							$page->addError("Error adding question: " . $question->error());
						} else {
							$page->appendSuccess("Question added.");
						}
					}
				}
			}
		}
	}

	// Load Questions if we have a valid form
	$questions = array();
	if ($can_proceed && $form->id) {
		$questions = $form->questions();
		if ($form->error()) {
			$page->addError($form->error());
		}
	}

	$page->setAdminMenuSection("Forms");  // Keep Forms section open
	if ($version->exists()) {
		$page->title("Edit Version ".$version->name);
		$page->addBreadcrumb("Forms","/_form/admin_forms");
		$page->addBreadcrumb($form->title,"/_form/admin_form/".$form->code);
	} else {
		$page->title("Add Form Version");
		$page->addBreadcrumb("Forms","/_form/admin_forms");
		$page->addBreadcrumb("Add Form");
	}