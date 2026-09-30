<?php
	/** @view /_form/admin_forms
	 * View for browsing forms.  Displays a list of forms with their active version and activation date.  Provides a link to add a new form and edit existing forms.
	 */
	$page = new \Site\Page();
	$page->setAdminMenuSection("Site");

	// Get List of Forms to Display
	$formList = new \Form\FormList();
	$forms = $formList->find();
	if ($formList->error()) {
		$page->addError($formList->error());
	};