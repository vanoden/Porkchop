<?php
/** Staff only: preview any version (draft or published). /_form/preview/{version_id} or ?_form/preview&version_id= */

$porkchop = new \Porkchop();
$site = $porkchop->site();
$page = $site->page();
$page->requirePrivilege('manage forms');

$form = new \Form\Form();
$version = new \Form\Version();

$vid = 0;
if (! empty($GLOBALS['_REQUEST_']->query_vars_array[0]) && ctype_digit((string)$GLOBALS['_REQUEST_']->query_vars_array[0])) {
	$vid = (int)$GLOBALS['_REQUEST_']->query_vars_array[0];
}
elseif (isset($_REQUEST['version_id']) && $form->validInteger($_REQUEST['version_id'])) {
	$vid = (int)$_REQUEST['version_id'];
}

if ($vid < 1) {
	$page->addError('No version selected. Use /_form/preview/{version_id} (numeric id) or pass version_id.');
}
else {
	$version = new \Form\Version($vid);
	if (! $version->exists()) {
		$page->addError('Form version not found.');
	}
	else {
		$form = new \Form\Form((int)$version->form_id);
		if (! $form->exists()) {
			$page->addError('Form not found.');
		}
	}
}

$extraHiddens = array();
if (! empty($_REQUEST['object_type']) && $form->validText((string)$_REQUEST['object_type'])) {
	$extraHiddens['object_type'] = (string)$_REQUEST['object_type'];
}
if (isset($_REQUEST['object_id']) && $form->validInteger($_REQUEST['object_id'])) {
	$extraHiddens['object_id'] = (string)(int)$_REQUEST['object_id'];
}

if (! $page->errorCount() && $form->exists() && $version->exists() && ! empty($_POST['form_submit'])) {
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
		$page->addError('Invalid request. Please reload the page and try again.');
	}
	else {
		$pv = isset($_POST['preview_version_id']) ? (int)$_POST['preview_version_id'] : 0;
		if ($pv !== (int)$version->id) {
			$page->addError('Invalid preview request.');
		}
		else {
			$answers = $_POST['answer'] ?? array();
			$ot = $_POST['object_type'] ?? null;
			$oi = isset($_POST['object_id']) ? (int)$_POST['object_id'] : null;
			if ($oi === 0) {
				$oi = null;
			}
			$result = $form->submitAnswersForVersion($version, is_array($answers) ? $answers : array(), $ot, $oi);
			if ($result['success']) {
				$page->appendSuccess('Thank you. Your submission was recorded for this version (preview / test).');
			}
			else {
				foreach ($result['errors'] as $err) {
					$page->addError($err);
				}
			}
		}
	}
}

if ($form->exists() && $version->exists()) {
	$page->title('Preview: ' . strip_tags($form->title) . ' — version ' . strip_tags((string)$version->name));
	$page->setAdminMenuSection("Site");
}
