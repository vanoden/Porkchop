<?php

// Return 404 to exclude from testing for now
header("HTTP/1.0 404 Not Found");
exit;

	$page = new \Site\Page();
	$page->requirePrivilege('manage product builds');

	$product = new \Build\Product();
	$can_proceed = true;

	// Validate id
	if ($product->validInteger($_REQUEST['id'] ?? null)) {
		$product = new \Build\Product($_REQUEST['id']);
	} else {
		$page->addError('Invalid product ID.');
		$can_proceed = false;
	}

	// Validate description
	if (!$product->validString($_REQUEST['description'] ?? null)) {
		$page->addError('Description is required.');
		$can_proceed = false;
	}

	// Validate major_version
	if (!$product->validInteger($_REQUEST['major_version'] ?? null)) {
		$page->addError('Invalid major version.');
		$can_proceed = false;
	}

	// Validate minor_version
	if (!$product->validInteger($_REQUEST['minor_version'] ?? null)) {
		$page->addError('Invalid minor version.');
		$can_proceed = false;
	}

	// Validate workspace
	if (!$product->validString($_REQUEST['workspace'] ?? null)) {
		$page->addError('Workspace is required.');
		$can_proceed = false;
	}

	if ($can_proceed) {
		if (isset($_REQUEST['btn_submit'])) {
			$parameters = array(
				"description"	=> $_REQUEST['description'],
				"major_version"	=> $_REQUEST['major_version'],
				"minor_version" => $_REQUEST['minor_version'],
				"workspace"		=> $_REQUEST['workspace']
			);
			
			if (! $product->update($parameters)) $page->addError($product->error());
		}
	}
