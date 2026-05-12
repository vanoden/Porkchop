<?php
	/** @view /_form/form
	 * Public view: render the published form HTML for optional version id (database id of form_versions).
	 * URL: /_form/form/{form_code} or /_form/form/{form_code}/{published_version_id}
	 */
	$page = new \Site\Page();
	$formHtml = '';

	$code = isset($GLOBALS['_REQUEST_']->query_vars_array[0])
		? trim(rawurldecode((string)$GLOBALS['_REQUEST_']->query_vars_array[0]))
		: '';
	if ($code === '' && ! empty($_REQUEST['code'])) {
		$code = trim(rawurldecode((string)$_REQUEST['code']));
	}
	$versionSeg = $GLOBALS['_REQUEST_']->query_vars_array[1] ?? null;
	$publishedVersionId = null;
	if ($versionSeg !== null && $versionSeg !== '') {
		if (! ctype_digit((string)$versionSeg)) {
			$page->addError('Invalid form version.');
		}
		else {
			$publishedVersionId = (int)$versionSeg;
		}
	}

	$isPostSubmit = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
		&& isset($_POST['form_submit']);
	$submissionOk = false;
	$postSubmitForm = null;

	if ($isPostSubmit) {
		if (! isset($GLOBALS['_SESSION_'])
			|| ! is_object($GLOBALS['_SESSION_'])
			|| ! method_exists($GLOBALS['_SESSION_'], 'verifyCSRFToken')
			|| ! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')
		) {
			$page->addError('Invalid or expired security token. Please reload the page and try again.');
		}
		else {
			$submitCode = trim((string)($_POST['form_code'] ?? ''));
			$subForm = new \Form\Form();
			$sfLoaded = false;
			if ($submitCode !== '') {
				if (ctype_digit($submitCode) && (int)$submitCode > 0) {
					$byId = new \Form\Form((int)$submitCode);
					if ($byId->exists()) {
						$subForm = $byId;
						$sfLoaded = true;
					}
				}
				if (! $sfLoaded) {
					$sfLoaded = $subForm->loadByFlexibleCode($submitCode);
				}
			}
			if (! $sfLoaded || ! $subForm->exists()) {
				$page->addError('Form not found for submission.');
			}
			else {
				$answers = isset($_POST['answer']) && is_array($_POST['answer']) ? $_POST['answer'] : array();
				// Public URL always submits against the published (active) version; ignore preview_version_id if forged.
				$result = $subForm->submitAnswers($answers, null, null);
				if (! empty($result['success'])) {
					$submissionOk = true;
					$page->appendSuccess('Thank you. Your submission was saved.');
				}
				else {
					foreach ($result['errors'] ?? array() as $err) {
						$page->addError((string)$err);
					}
					$postSubmitForm = $subForm;
				}
			}
		}
	}

	$form = new \Form\Form();
	$loaded = false;
	if ($postSubmitForm !== null) {
		$form = $postSubmitForm;
		$loaded = true;
	}
	elseif ($code !== '') {
		if (ctype_digit($code) && (int)$code > 0) {
			$byId = new \Form\Form((int)$code);
			if ($byId->exists()) {
				$form = $byId;
				$loaded = true;
			}
		}
		if (! $loaded) {
			$loaded = $form->loadByFlexibleCode($code);
		}
	}

	if ($code === '') {
		$page->addError('Form code is required. Use /_form/form/{code} or ?code={code}.');
	}
	elseif (! $postSubmitForm && ! $page->errorCount() && ! $loaded) {
		$page->addError(
			'No form in this database with code "'
			. htmlspecialchars($code, ENT_QUOTES, 'UTF-8')
			. '". In Admin open Forms and use the same code shown there (or open the form and check the browser URL). '
			. 'Forms are per site/database—create the form on this host or fix the code you are using.'
		);
	}

	$showForm = $form->exists()
		&& ! $submissionOk
		&& (
			$page->errorCount() === 0
			|| $postSubmitForm !== null
			|| ($isPostSubmit && $loaded && $code !== '')
		);

	if ($submissionOk) {
		if ($form->exists()) {
			$page->title(htmlspecialchars((string)$form->title));
		}
		else {
			$page->title('Form');
		}
	}
	elseif ($showForm) {
		$formHtml = $form->render($publishedVersionId);
		$page->title(htmlspecialchars((string)$form->title));
	}
	elseif ($form->exists()) {
		$page->title(htmlspecialchars((string)$form->title));
	}
	else {
		$page->title('Form');
	}
