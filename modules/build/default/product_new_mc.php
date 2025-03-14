<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage product builds');
	$product = new \Build\Product();

	if (isset($_REQUEST['btn_submit'])) {
		$can_proceed = true;

		// Validate name
		if (!$product->validString($_REQUEST['name'] ?? null)) {
			$page->addError('Product name is required.');
			$can_proceed = false;
		}

		// Validate architecture
		if (!$product->validString($_REQUEST['architecture'] ?? null)) {
			$page->addError('Architecture is required.');
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
			$parameters = array(
				'name'	=> $_REQUEST['name'],
				'architecture'	=> $_REQUEST['architecture'],
				'description'	=> $_REQUEST['description'],
				'major_version'	=> $_REQUEST['major_version'],
				'minor_version'	=> $_REQUEST['minor_version'],
				'workspace'		=> $_REQUEST['workspace']
			);
			
			if (!$product->add($parameters)) $page->addError($product->error());
			else {
				header("location: /_build/product?id=".$product->id);
				return;
			}
		}
	}
