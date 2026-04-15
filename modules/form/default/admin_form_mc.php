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

	// Load Form based on parameters
	if ($_POST['id'] ?? false) {
		$form = new \Form\Form($_POST['id']);
		if (!$form->exists()) {
			$page->addError("Form not found!");
		}
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$form = new \Form\Form();
		if (! $form->get($GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$page->addError("Form not found!");
		}
	}
	else {
		$form = new \Form\Form();
		$form->code = $porkchop->biguuid();
	}

	if (!empty($_POST['submit'])) {
		// Validate CSRF Token
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrf_token'] ?? '')) $page->addError("Invalid Request, please reload the form and try again.");
		else {
			if (empty($_REQUEST['code']) || !$form->validText($_REQUEST['code'])) {
				$page->addError("Invalid or missing form code");
			}
			elseif (!$form->validCode($_REQUEST['code'])) {
				$page->addError("Form code can only contain letters, numbers, underscores, and dashes");
			}
			if (empty($_REQUEST['title']) || !$form->validText($_REQUEST['title'])) {
				$page->addError("Invalid or missing form title");
			}
			elseif (!$form->validTitle($_REQUEST['title'])) {
				$page->addError("Form title can only contain letters, numbers, spaces, underscores, and dashes");
			}
			if (empty($_REQUEST['method']) || !$form->validMethod($_REQUEST['method'])) {
				$page->addError("Invalid form method");
			}
			elseif (!$form->validMethod($_REQUEST['method'] ?? '')) {
				$page->addError("Invalid form method");
			}
			if (!empty($_REQUEST['action']) && !$form->validAction($_REQUEST['action'])) {
				$page->addError("Invalid form action URL");
			}

			if (!$page->errorCount()) {
				$parameters = [
					'title' => $_POST['title'] ?? '',
					'description' => $_POST['description'] ?? '',
					'action' => $_POST['action'] ?? '',
					'method' => $_POST['method'] ?? 'post',
				];
				// Instructions are edited on versions; preserve existing form-level value unless explicitly posted.
				if (array_key_exists('instructions', $_POST)) {
					$parameters['instructions'] = $_POST['instructions'];
				}
				if (!$form->exists()) {
					// Add new form
					$parameters['code'] = $_POST['code'] ?? $form->code;
					if ($form->add($parameters)) {
						$page->appendSuccess("Form added successfully.");
					} else {
						$page->addError("Error adding form: " . $form->error());
					}
				}
				else {
					// Update existing form
					if ($form->update($parameters)) {
						$page->appendSuccess("Form updated successfully.");
					} else {
						$page->addError("Error updating form: " . $form->error());
					}
				}
			}
		}
	}

	// Load Versions if we have a valid form
	$versions = array();
	if ($can_proceed && $form->id) {
		$versions = $form->versions();
		if ($form->error()) {
			$page->addError($form->error());
		}
	}

	if ($form->exists()) {
		$page->title("Edit Form");
		$page->setAdminMenuSection("Site");
		$page->addBreadcrumb("Forms","/_form/admin_forms");
		$page->addBreadcrumb($form->title,"/_form/admin_form/".$form->code);
	} else {
		$page->title("Add Form");
		$page->setAdminMenuSection("Site");
		$page->addBreadcrumb("Forms","/_form/admin_forms");
		$page->addBreadcrumb("Add Form");
	}
