<?php
	/** @view /_form/browse
	 * View for browsing forms.  Displays a list of forms with their active version and activation date.  Provides a link to add a new form and edit existing forms.
	 */
	// Load Page
	$porkchop = new \Porkchop();
	$site = $porkchop->site();
	$page = $site->page();
	$page->setAdminMenuSection("Site");

	// Get List of Forms to Display
	$formList = new \Form\FormList();
	$forms = $formList->find();
	if ($formList->error()) {
		$page->addError($formList->error());
	};