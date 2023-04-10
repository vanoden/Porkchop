<?php
###########################################
### /_form/show/<code>					###
### Display the form identified by		###
### <code> or with id post param.		###
###########################################

# Load Page Info
$site = new \Site();
$page = $site->page();

# Load Form
if (!empty($_REQUEST['code'])) {
	$form = new \Form\Form();
	$form->get($_REQUEST['code']);
} elseif (!empty($_REQUEST['id'])) {
	$form = new \Form\Form($_REQUEST['id']);
} elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
	$form = new \Form\Form();
	$form->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
}

if (!$form->exists()) {
	$page->addError("Form not found!");
} else {
	# Load Questions
	$questions = $form->questions();
}