<?php
	#######################################
	### /_form/browse					###
	### List available forms with link	###
	### to edit and button to add new.	###
	#######################################

	$page = new \Site\Page();

	$formList = new \Form\FormList();
	$forms = $formList->find();
	if ($formList->error()) {
		$page->addError($formList->error());
	};