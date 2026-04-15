<?php
###########################################
### /_form/show/<code>					###
### Public form display and submission	###
###########################################

$porkchop = new \Porkchop();
$site = $porkchop->site();
$page = $site->page();

$form = new \Form\Form();
$code = $_REQUEST['code'] ?? '';
$query_var = $GLOBALS['_REQUEST_']->query_vars_array[0] ?? null;
if (! strlen($code) && ! empty($query_var)) {
	$code = $query_var;
}

if (empty($code) || ! $form->validCode($code)) {
	$page->addError("No valid form identifier provided");
}
elseif (! $form->get($code)) {
	$page->addError($form->error() ?: "Form not found");
}

$extraHiddens = array();
if (! empty($_REQUEST['object_type']) && $form->validText((string)$_REQUEST['object_type'])) {
	$extraHiddens['object_type'] = (string)$_REQUEST['object_type'];
}
if (isset($_REQUEST['object_id']) && $form->validInteger($_REQUEST['object_id'])) {
	$extraHiddens['object_id'] = (string)(int)$_REQUEST['object_id'];
}

if (! $page->errorCount() && $form->exists() && ! empty($_POST['form_submit'])) {
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'] ?? '')) {
		$page->addError("Invalid request. Please reload the page and try again.");
	}
	else {
		$answers = $_POST['answer'] ?? array();
		$ot = $_POST['object_type'] ?? null;
		$oi = isset($_POST['object_id']) ? (int)$_POST['object_id'] : null;
		if ($oi === 0) {
			$oi = null;
		}
		$result = $form->submitAnswers(is_array($answers) ? $answers : array(), $ot, $oi);
		if ($result['success']) {
			$page->appendSuccess("Thank you. Your submission was received.");
		}
		else {
			foreach ($result['errors'] as $err) {
				$page->addError($err);
			}
		}
	}
}

if ($form->exists()) {
	$page->title(strip_tags($form->title));
}
