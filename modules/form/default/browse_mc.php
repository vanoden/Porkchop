<?php
	#######################################
	### /_form/browse					###
	### List available forms with link	###
	### to edit and button to add new.	###
	#######################################

	# Load Page
	$site = new \Site();
	$page = $site->page();

	$formList = new \Form\FormList();
	$forms = $formList->find();
	if ($formList->error()) {
		$page->addError($formList->error());
	};