<?php
	/** Admin: submissions for one form */
	$page = new \Site\Page();
	$page->requirePrivilege('manage forms');
	$page->setAdminMenuSection("Site");

	$formCode = isset($GLOBALS['_REQUEST_']->query_vars_array[0])
		? trim((string)$GLOBALS['_REQUEST_']->query_vars_array[0]) : '';

	$form = new \Form\Form();
	if ($formCode === '' || ! $form->get($formCode)) {
		$page->addError($formCode === '' ? "Form code required." : ("Form not found: " . $formCode));
		$can_proceed = false;
	}
	else {
		$can_proceed = true;
		$list = new \Form\SubmissionList();
		$submissions = $list->find(array(
			'form_id' => (int)$form->id,
			'_sort' => 'date_submitted',
			'_order' => 'DESC',
		));
		if ($list->error()) {
			$page->addError($list->error());
			$can_proceed = false;
			$submissions = array();
		}
		elseif (! is_array($submissions)) {
			$submissions = array();
		}
	}

	if ($can_proceed) {
		$page->title("Submissions · " . (string)$form->title);
		$page->addBreadcrumb("Forms", "/_form/admin_forms");
		$page->addBreadcrumb($form->title, "/_form/admin_form/" . $form->code);
	}
