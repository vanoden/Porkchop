<?php
	/** Admin: one submission’s answer rows */
	$page = new \Site\Page();
	$page->requirePrivilege('manage forms');
	$page->setAdminMenuSection("Site");

	$submissionIdRaw = isset($GLOBALS['_REQUEST_']->query_vars_array[0])
		? trim((string)$GLOBALS['_REQUEST_']->query_vars_array[0]) : '';
	$submissionId = ctype_digit($submissionIdRaw) ? (int)$submissionIdRaw : 0;

	$submission = new \Form\Submission($submissionId);
	$can_proceed = $submissionId > 0 && $submission->exists();
	$form = new \Form\Form();
	$answerRows = array();
	$submissionMeta = array(
		'date_submitted' => '',
		'remote_addr' => '',
		'version_label' => '',
	);

	if (! $can_proceed) {
		$page->addError("Submission not found.");
	}
	else {
		$form = new \Form\Form((int)$submission->form_id);
		if (! $form->exists()) {
			$page->addError("Form not found for this submission.");
			$can_proceed = false;
		}
		else {
			$submissionMeta['date_submitted'] = (string)($submission->date_submitted ?? '');
			$submissionMeta['remote_addr'] = (string)($submission->remote_addr ?? '');
			$ver = new \Form\Version((int)$submission->version_id);
			$submissionMeta['version_label'] = $ver->exists()
				? (string)$ver->name : ('#' . (int)$submission->version_id);

			$answersRaw = $submission->answers();
			foreach ($answersRaw as $ans) {
				if (! $ans instanceof \Form\Submission\Answer) {
					continue;
				}
				$q = new \Form\Question((int)($ans->question_id ?? 0));
				$row = array(
					'id' => (int)($ans->id ?? 0),
					'submission_id' => (int)($ans->submission_id ?? 0),
					'question_id' => (int)($ans->question_id ?? 0),
					'question_text' => $q->exists() ? (string)$q->text : '—',
					'question_prompt' => $q->exists() ? (string)$q->prompt : '',
					'aggregate_key' => (string)($ans->aggregate_key ?? ''),
					'value_raw' => (string)($ans->value ?? ''),
				);
				$disp = trim($row['value_raw']);
				if ($disp !== '' && strlen($disp) > 1 && ($disp[0] === '[' || $disp[0] === '{')) {
					$j = json_decode($disp, true);
					if (is_array($j)) {
						$disp = implode(', ', array_map('strval', $j));
					}
				}
				$row['value_display'] = $disp;
				$answerRows[] = $row;
			}

			$page->title("Submission #" . $submissionId);
			$page->addBreadcrumb("Forms", "/_form/admin_forms");
			$page->addBreadcrumb($form->title, "/_form/admin_form/" . $form->code);
			$page->addBreadcrumb("Submissions", "/_form/admin_submissions/" . $form->code);
			$page->addBreadcrumb("#" . $submissionId);
		}
	}
