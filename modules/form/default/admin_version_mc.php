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
	$can_proceed = true;

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
		$paths = $GLOBALS['_REQUEST_']->query_vars_array;
		$seg0 = $paths[0];
		$seg1 = $paths[1] ?? null;

		$form = new \Form\Form();
		$formLoaded = $form->get($seg0);

		if ($formLoaded) {
			if ($seg1 !== null && $seg1 !== '') {
				if ($form->validInteger((string)$seg1)) {
					$version = new \Form\Version((int)$seg1);
					if (! $version->exists() || (int)$version->form_id !== (int)$form->id) {
						$page->addError("Form version not found!");
					}
				} else {
					$version = new \Form\Version();
					if (! $version->get($seg1) || (int)$version->form_id !== (int)$form->id) {
						$page->addError("Form version not found!");
					}
				}
			} else {
				$versionList = new \Form\VersionList();
				$version = new \Form\Version();
				$version->form_id = $form->id;
				$version->name = $versionList->nextVersionNumber($form->id);
			}
		} elseif ($seg1 === null && $form->validInteger((string)$seg0)) {
			// e.g. /_form/admin_version/4 — version id from admin form list (not form code)
			$version = new \Form\Version((int)$seg0);
			if (! $version->exists()) {
				$page->addError("Form version not found!");
			} else {
				$form = new \Form\Form($version->form_id);
				if (! $form->exists()) {
					$page->addError("Form not found for this version!");
				}
			}
		} else {
			$page->addError("Form not found!");
		}
	}
	if (!$page->errorCount() && !isset($version)) {
		$form->code = $porkchop->biguuid();
	}

	if (!empty($_POST)) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrf_token'] ?? '')) {
			$page->addError("Invalid Request, please reload the form and try again.");
		}
	}

	if (!empty($_POST) && !$page->errorCount()) {
		if (!empty($_POST['publish_version'])) {
			if (! isset($version) || ! $version->exists()) {
				$page->addError("Cannot publish: version not found.");
			} elseif ($version->active()) {
				$page->appendSuccess("This version is already published.");
			} else {
				$uid = isset($GLOBALS['_SESSION_']->customer->id) ? (int)$GLOBALS['_SESSION_']->customer->id : null;
				if (! $version->publish($uid)) {
					$page->addError($version->error() ?: "Publish failed.");
				} else {
					$page->appendSuccess("This version is now published (live).");
					$form = new \Form\Form((int)$form->id);
				}
			}
		}
		elseif (!empty($_POST['unpublish_form'])) {
			if (! $form->exists()) {
				$page->addError("Form not found.");
			} elseif (empty($form->active_version_id)) {
				$page->addError("No published version to unpublish.");
			} else {
				$currentActiveId = (int)$form->active_version_id;
				$fallback = null;
				$versionList = new \Form\VersionList();
				$allVersions = $versionList->find(array(
					'form_id' => (int)$form->id,
					'_sort' => 'id',
					'_order' => 'DESC',
				));
				if ($versionList->error()) {
					$page->addError($versionList->error());
				} else {
					foreach ($allVersions as $candidate) {
						$cid = (int)($candidate->id ?? 0);
						if ($cid < 1 || $cid === $currentActiveId) {
							continue;
						}
						// Prefer the next older version; if not found, use newest other version.
						if ($fallback === null) {
							$fallback = $candidate;
						}
						if ($cid < $currentActiveId) {
							$fallback = $candidate;
							break;
						}
					}
					if ($fallback && $fallback->exists()) {
						if (! $form->setActiveVersion($fallback)) {
							$page->addError($form->error() ?: "Unpublish failed.");
						} else {
							$page->appendSuccess("Reverted to previous version: " . (string)$fallback->name . ".");
							$form = new \Form\Form((int)$form->id);
						}
					}
					elseif (! $form->clearActiveVersion()) {
						$page->addError($form->error() ?: "Unpublish failed.");
					} else {
						$page->appendSuccess("Form is no longer published.");
						$form = new \Form\Form((int)$form->id);
					}
				}
			}
		}
		elseif (isset($_POST['submit'])) {
		// Validate Parameters (save)
		$versionCode = trim((string)($_POST['code'] ?? ''));
		if ($versionCode !== '' && ! $form->validCode($versionCode)) {
			$page->addError("Invalid version code");
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

		if ($versionCode === '') {
			if ($version->exists()) {
				$versionCode = (string)($version->code ?? '');
			} else {
				$versionCode = $porkchop->biguuid();
			}
		}
		$parameters = array(
			'code' => $versionCode,
			'name' => $_POST['name'] ?? '',
			'action' => $_POST['action'] ?? '',
			'method' => $_POST['method'] ?? '',
			'description' => $_POST['description'] ?? '',
			'instructions' => $_POST['instructions'] ?? '',
		);
		if (! $page->errorCount()) {
			if (! $version->exists()) {
				$sourceVersion = null;
				$existingVersions = $form->versions();
				if (! empty($existingVersions)) {
					$sourceVersion = $existingVersions[0];
				}
				$parameters['form_id'] = (int)$form->id;
				if (! $version->add($parameters)) {
					$page->addError("Error adding version: " . $version->error());
				} else {
					$page->appendSuccess("Version added.");
					if ($sourceVersion && $sourceVersion->exists()) {
						if (! $version->copyQuestionsFrom($sourceVersion)) {
							$page->addError("Error copying questions from previous version: " . $version->error());
							$can_proceed = false;
						} else {
							$page->appendSuccess(
								"Copied questions/options from version '" . (string)$sourceVersion->name .
								"' (" . (string)$sourceVersion->code . ") to new version '" .
								(string)$version->name . "' (" . (string)$version->code . ")."
							);
						}
					}
				}
			}
			else {
				if (!$version->update($parameters)) {
					$page->addError("Error updating version: " . $version->error());
					$can_proceed = false;
				} else {
					$page->appendSuccess("Version updated.");
				}
			}
		}

		// Process questions if form was successfully created/updated
		if ($can_proceed) {
			$typesPost = $_REQUEST['type'] ?? array();
			if (! is_array($typesPost)) {
				$typesPost = array();
			}
			foreach ($typesPost as $question_id => $question_type) {
				$question_id = (int)$question_id;
				if ($question_id < 1) {
					continue;
				}
				$question = new \Form\Question($question_id);
				if (! $question->exists() || (int)$question->version_id !== (int)$version->id) {
					$page->addError("Invalid question in this version.");
					$can_proceed = false;
					continue;
				}

				if (! $question->validType($question_type)) {
					$page->addError("Invalid question type: " . $question_type);
					$can_proceed = false;
					continue;
				}

				$question_text = $_REQUEST['text'][$question_id] ?? '';
				if ($question_text === '' || $question_text === null) {
					$page->addError("Question text is required");
					$can_proceed = false;
				} elseif (! $question->validText($question_text)) {
					$page->addError("Invalid question text format");
					$can_proceed = false;
				}
				$question_prompt = $_REQUEST['prompt'][$question_id] ?? '';
				if ($question_prompt === '' || $question_prompt === null) {
					$page->addError("Question prompt is required");
					$can_proceed = false;
				} elseif (! $question->validText($question_prompt)) {
					$page->addError("Invalid question prompt format");
					$can_proceed = false;
				}
				$question_required = ! empty($_REQUEST['required'][$question_id]) ? 1 : 0;
				if (! $question->validInteger((string)$question_required)) {
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

					if (! $question->update($parameters)) {
						$page->addError("Error updating question: " . $question->error());
						$can_proceed = false;
					} else {
						$page->appendSuccess("Question updated.");
					}
				}
			}

			// Select / radio / checkbox options
			if ($can_proceed && $version->exists()) {
				$choiceTypes = array('select', 'radio', 'checkbox');
				// Deletes first
				$optionDeletes = $_POST['option_delete'] ?? array();
				if (is_array($optionDeletes)) {
					foreach ($optionDeletes as $option_id => $flag) {
						if (empty($flag)) {
							continue;
						}
						$option_id = (int)$option_id;
						if ($option_id < 1) {
							continue;
						}
						$opt = new \Form\Question\Option($option_id);
						if (! $opt->exists()) {
							continue;
						}
						$q = new \Form\Question((int)$opt->question_id);
						if (! $q->exists() || (int)$q->version_id !== (int)$version->id || ! in_array($q->type, $choiceTypes, true)) {
							continue;
						}
						if (! $opt->drop()) {
							$page->addError("Error removing choice: " . $opt->error());
							$can_proceed = false;
						} else {
							$page->appendSuccess("Choice removed.");
						}
					}
				}
				// Updates
				$optionTexts = $_POST['option_text'] ?? array();
				if ($can_proceed && is_array($optionTexts)) {
					foreach ($optionTexts as $option_id => $optText) {
						$option_id = (int)$option_id;
						if ($option_id < 1 || ! empty($_POST['option_delete'][$option_id])) {
							continue;
						}
						$opt = new \Form\Question\Option($option_id);
						if (! $opt->exists()) {
							continue;
						}
						$q = new \Form\Question((int)$opt->question_id);
						if (! $q->exists() || (int)$q->version_id !== (int)$version->id || ! in_array($q->type, $choiceTypes, true)) {
							continue;
						}
						$optText = trim((string)$optText);
						$optVal = trim((string)($_POST['option_value'][$option_id] ?? ''));
						if ($optText === '' || $optVal === '') {
							$page->addError("Choice label and value cannot be empty (option #" . $option_id . ").");
							$can_proceed = false;
							continue;
						}
						if (! $form->validText($optText) || ! $form->validText($optVal)) {
							$page->addError("Invalid choice text for option #" . $option_id . ".");
							$can_proceed = false;
							continue;
						}
						if (! $opt->update(array(
							'text' => $optText,
							'value' => $optVal,
						))) {
							$page->addError("Error updating choice: " . $opt->error());
							$can_proceed = false;
						}
					}
				}
				// New options per question (multiple rows: option_new_text[qid][] and option_new_value[qid][])
				$optionNewText = $_POST['option_new_text'] ?? array();
				if ($can_proceed && is_array($optionNewText)) {
					foreach ($optionNewText as $qid => $texts) {
						$qid = (int)$qid;
						if ($qid < 1) {
							continue;
						}
						if (! is_array($texts)) {
							$texts = array($texts);
						}
						$vals = $_POST['option_new_value'][$qid] ?? array();
						if (! is_array($vals)) {
							$vals = array($vals);
						}
						$q = new \Form\Question($qid);
						$hasNonEmpty = false;
						$nPairs = max(count($texts), count($vals));
						for ($i = 0; $i < $nPairs; $i++) {
							$t = trim((string)($texts[$i] ?? ''));
							$v = trim((string)($vals[$i] ?? ''));
							if ($t !== '' || $v !== '') {
								$hasNonEmpty = true;
								break;
							}
						}
						if ($hasNonEmpty && (! $q->exists() || (int)$q->version_id !== (int)$version->id || ! in_array($q->type, $choiceTypes, true))) {
							$page->addError("Invalid question for new choice.");
							$can_proceed = false;
							continue;
						}
						if (! $q->exists() || (int)$q->version_id !== (int)$version->id || ! in_array($q->type, $choiceTypes, true)) {
							continue;
						}
						$maxSort = 0;
						foreach ($q->options() as $o) {
							$maxSort = max($maxSort, (int)$o->sort_order);
						}
						for ($i = 0; $i < $nPairs; $i++) {
							$nText = trim((string)($texts[$i] ?? ''));
							$nVal = trim((string)($vals[$i] ?? ''));
							if ($nText === '' && $nVal === '') {
								continue;
							}
							if ($nText === '' || $nVal === '') {
								$page->addError("New choice needs both label and value (question #" . $qid . ").");
								$can_proceed = false;
								break;
							}
							if (! $form->validText($nText) || ! $form->validText($nVal)) {
								$page->addError("Invalid new choice text (question #" . $qid . ").");
								$can_proceed = false;
								break;
							}
							$maxSort += 10;
							if (! $q->addOption(array(
								'text' => $nText,
								'value' => $nVal,
								'sort_order' => $maxSort,
							))) {
								$page->addError("Error adding choice: " . $q->error());
								$can_proceed = false;
								break;
							}
							$page->appendSuccess("Choice added.");
						}
					}
				}
			}

			// Add new question if provided
			if ($can_proceed && ! empty($_REQUEST['text_new'])) {
				$question = new \Form\Question();
				$text_new = trim(noXSS($_REQUEST['text_new'] ?? ''));

				if (! $question->validType($_REQUEST['type_new'] ?? '')) {
					$page->addError("Invalid question type: " . $_REQUEST['type_new']);
				} elseif (empty($_REQUEST['text_new'])) {
					$page->addError("Question text is required.");
				} else {
					if (empty($_REQUEST['prompt_new'])) {
						$page->addError("Question prompt is required");
					} elseif (! $question->validText($_REQUEST['prompt_new'] ?? '')) {
						$page->addError("Invalid question prompt format");
					}

					$reqNew = ! empty($_REQUEST['required_new']) ? 1 : 0;
					if (! $question->validInteger((string)$reqNew)) {
						$page->addError("Invalid question required format");
						$can_proceed = false;
					}
					if (! $page->errorCount() && $can_proceed) {
						$parameters = array(
							'version_id' => $version->id,
							'type' => $_REQUEST['type_new'] ?? '',
							'text' => $_REQUEST['text_new'] ?? '',
							'prompt' => $_REQUEST['prompt_new'] ?? '',
							'required' => $reqNew,
						);

						if (! $question->add($parameters)) {
							$page->addError("Error adding question: " . $question->error());
						} else {
							$page->appendSuccess("Question added.");
						}
					}
				}
			}
		}
		}
	}

	// Load Questions for this version
	$questions = array();
	if ($can_proceed && $form->id && isset($version) && $version->exists()) {
		$questions = $version->questions();
	}

	$page->setAdminMenuSection("Site");
	if (isset($version) && $version->exists()) {
		$page->title("Edit Version ".$version->name);
		$page->addBreadcrumb("Forms","/_form/admin_forms");
		$page->addBreadcrumb($form->title,"/_form/admin_form/".$form->code);
	} else {
		$page->title("Add Form Version");
		$page->addBreadcrumb("Forms","/_form/admin_forms");
		$page->addBreadcrumb("Add Form");
	}