<?php
	/** @view /_form/admin_version_preview
	 * Staff preview of a form version (draft or non-live). Requires manage forms.
	 * URL: /_form/admin_version_preview/{version_id}
	 */
	$page = new \Site\Page();
	$page->requirePrivilege('manage forms');
	$page->setAdminMenuSection('Site');

	$formHtml = '';
	$versionIdRaw = isset($GLOBALS['_REQUEST_']->query_vars_array[0])
		? trim((string)$GLOBALS['_REQUEST_']->query_vars_array[0]) : '';
	$versionId = ctype_digit($versionIdRaw) ? (int)$versionIdRaw : 0;

	$version = new \Form\Version($versionId);
	$form = new \Form\Form();
	$submissionOk = false;
	$postSubmitForm = null;

	if ($versionId < 1 || ! $version->exists()) {
		$page->addError('Form version not found.');
	}
	else {
		$form = new \Form\Form((int)$version->form_id);
		if (! $form->exists()) {
			$page->addError('Form not found for this version.');
		}
	}

	$isPostSubmit = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
		&& isset($_POST['form_submit']);

	if ($isPostSubmit && ! $page->errorCount()) {
		if (! isset($GLOBALS['_SESSION_'])
			|| ! is_object($GLOBALS['_SESSION_'])
			|| ! method_exists($GLOBALS['_SESSION_'], 'verifyCSRFToken')
			|| ! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')
		) {
			$page->addError('Invalid or expired security token. Please reload the page and try again.');
		}
		else {
			$previewVersionId = (int)($_POST['preview_version_id'] ?? 0);
			if ($previewVersionId !== $versionId) {
				$page->addError('Invalid preview version.');
			}
			else {
				$submitCode = trim((string)($_POST['form_code'] ?? ''));
				if ($submitCode === '' || $submitCode !== (string)$form->code) {
					$page->addError('Form not found for submission.');
				}
				else {
					$answers = isset($_POST['answer']) && is_array($_POST['answer']) ? $_POST['answer'] : array();
					$result = $form->submitAnswersForVersion($version, $answers, null, null);
					if (! empty($result['success'])) {
						$submissionOk = true;
						$page->appendSuccess('Preview submission saved (same as a live submission).');
					}
					else {
						foreach ($result['errors'] ?? array() as $err) {
							$page->addError((string)$err);
						}
						$postSubmitForm = $form;
					}
				}
			}
		}
	}

	$showForm = $form->exists()
		&& $version->exists()
		&& ! $submissionOk
		&& (
			$page->errorCount() === 0
			|| $postSubmitForm !== null
			|| ($isPostSubmit && ! $submissionOk)
		);

	if ($showForm) {
		$formHtml = $form->renderPreview($version);
	}

	if ($version->exists() && $form->exists()) {
		$page->title('Preview: ' . (string)$version->name);
		$page->addBreadcrumb('Forms', '/_form/admin_forms');
		$page->addBreadcrumb($form->title, '/_form/admin_form/' . $form->code);
		$page->addBreadcrumb((string)$version->name, '/_form/admin_version/' . (int)$version->id);
		$page->addBreadcrumb('Preview');
	}
	else {
		$page->title('Form version preview');
		$page->addBreadcrumb('Forms', '/_form/admin_forms');
	}
