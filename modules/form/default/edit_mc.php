<?php
###########################################
### /_form/edit/<code>					###
### Allow add/edit/drop of questions	###
### for the form specified with <code>	###
### or the post param id.				###
###########################################


// Return 404 to exclude from testing for now
header("HTTP/1.0 404 Not Found");
exit;

	# Load Page
	$site = new \Site();
	$page = $site->page();
	$porkchop = new \Porkchop();
	$can_proceed = true;
	
	# Initialize form for validation
	$form = new \Form\Form();

	# Load Form based on parameters
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
			if ($form->error()) {
				$page->addError($form->error());
				$can_proceed = false;
			}
		}
	}

	$submit = $_REQUEST['submit'] ?? null;
	if (!empty($submit) && $can_proceed) {
		if (!$form->exists()) {
			// Add new form
			$new_code = $_REQUEST['code'] ?? '';
			if (empty($new_code)) {
				$new_code = $porkchop->uuid();
			} elseif (!$form->validText($new_code)) {
				$page->addError("Invalid form code format");
				$can_proceed = false;
			}
			
			$method = $_REQUEST['method'] ?? 'POST';
			if (!$form->validMethod($method)) {
				$page->addError("Invalid method format");
				$can_proceed = false;
			}
			
			$action = $_REQUEST['action'] ?? '';
			if (!empty($action) && !$form->validAction($action)) {
				$page->addError("Invalid action format");
				$can_proceed = false;
			}
			
			$title = $_REQUEST['title'] ?? '';
			if (empty($title)) {
				$page->addError("Title is required");
				$can_proceed = false;
			} elseif (!$form->validText($title)) {
				$page->addError("Invalid title format");
				$can_proceed = false;
			}
			
			$description = $_REQUEST['description'] ?? '';
			$instructions = $_REQUEST['instructions'] ?? '';
			
			if ($can_proceed) {
				$parameters = array(
					'code' => $new_code,
					'title' => $title,
					'action' => $action,
					'method' => $method,
					'description' => $description,
					'instructions' => $instructions,
				);
				if (!$form->add($parameters)) {
					$page->addError("Error adding form: " . $form->error());
					$can_proceed = false;
				} else {
					$page->appendSuccess("Form added.");
				}
			}
		} else {
			// Update existing form
			$update_code = $_REQUEST['code'] ?? '';
			if (empty($update_code)) {
				$page->addError("Code is required");
				$can_proceed = false;
			} elseif (!$form->validText($update_code)) {
				$page->addError("Invalid form code format");
				$can_proceed = false;
			}
			
			$title = $_REQUEST['title'] ?? '';
			if (empty($title)) {
				$page->addError("Title is required");
				$can_proceed = false;
			} elseif (!$form->validText($title)) {
				$page->addError("Invalid title format");
				$can_proceed = false;
			}
			
			$active = $_REQUEST['active'] ?? 0;
			$method = $_REQUEST['method'] ?? 'POST';
			if (!$form->validMethod($method)) {
				$page->addError("Invalid method format");
				$can_proceed = false;
			}
			
			$description = $_REQUEST['description'] ?? '';
			if (empty($description)) {
				$page->addError("Description is required");
				$can_proceed = false;
			} elseif (!$form->validText($description)) {
				$page->addError("Invalid description format");
				$can_proceed = false;
			}
			
			$instructions = $_REQUEST['instructions'] ?? '';
			if (empty($instructions)) {
				$page->addError("Instructions are required");
				$can_proceed = false;
			} elseif (!$form->validText($instructions)) {
				$page->addError("Invalid instructions format");
				$can_proceed = false;
			}
			
			if ($can_proceed) {
				$parameters = array(
					'code' => $update_code,
					'title' => $title,
					'active' => $active,
					'method' => $method,
					'description' => $description,
					'instructions' => $instructions,
				);
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
			$type_new = $_REQUEST['type_new'] ?? '';
			if (!empty($type_new)) {
				$question = new \Form\Question();
				$text_new = trim(noXSS($_REQUEST['text_new'] ?? ''));

				if (!$question->validType($type_new)) {
					$page->addError("Invalid question type: " . $type_new);
				} elseif (empty($text_new)) {
					$page->addError("Question text is required.");
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
						
						$parameters = array(
							'form_id' => $form->id,
							'type' => $type_new,
							'text' => $text_new,
							'prompt' => $prompt_new,
							'required' => $required_new,
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

	$page->title("Edit Form");
	$page->setAdminMenuSection("Forms");  // Keep Forms section open
	$page->addBreadcrumb("Forms","/_form/browse");
