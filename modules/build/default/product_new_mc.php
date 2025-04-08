<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage product builds');
	$product = new \Build\Product();

	if (isset($_REQUEST['btn_submit'])) {
		$can_proceed = true;

		try {
			// Validate name
			$name = $_REQUEST['name'] ?? '';
			if (empty($name)) {
				$page->addError('Product name is required.');
				$can_proceed = false;
			} elseif (!$product->validName($name)) {
				$page->addError('Invalid product name format.');
				$can_proceed = false;
			}

			// Validate architecture
			$architecture = $_REQUEST['architecture'] ?? '';
			if (empty($architecture)) {
				$page->addError('Architecture is required.');
				$can_proceed = false;
			} elseif (!$product->validCode($architecture)) {
				$page->addError('Invalid architecture format.');
				$can_proceed = false;
			}

			// Validate description
			$description = $_REQUEST['description'] ?? '';
			if (empty($description)) {
				$page->addError('Description is required.');
				$can_proceed = false;
			} elseif (!$product->validText($description)) {
				$page->addError('Invalid description format.');
				$can_proceed = false;
			}

			// Validate major_version
			$major_version = $_REQUEST['major_version'] ?? '';
			if (empty($major_version)) {
				$page->addError('Major version is required.');
				$can_proceed = false;
			} elseif (!$product->validInteger($major_version)) {
				$page->addError('Major version must be a valid number.');
				$can_proceed = false;
			}

			// Validate minor_version
			$minor_version = $_REQUEST['minor_version'] ?? '';
			if (empty($minor_version)) {
				$page->addError('Minor version is required.');
				$can_proceed = false;
			} elseif (!$product->validInteger($minor_version)) {
				$page->addError('Minor version must be a valid number.');
				$can_proceed = false;
			}

			// Validate workspace
			$workspace = $_REQUEST['workspace'] ?? '';
			if (empty($workspace)) {
				$page->addError('Workspace is required.');
				$can_proceed = false;
			} elseif (!$product->validCode($workspace)) {
				$page->addError('Invalid workspace format.');
				$can_proceed = false;
			}

			if ($can_proceed) {
				$parameters = array(
					'name'	=> $name,
					'architecture'	=> $architecture,
					'description'	=> $description,
					'major_version'	=> $major_version,
					'minor_version'	=> $minor_version,
					'workspace'		=> $workspace
				);
				
				if (!$product->add($parameters)) {
					error_log("Product add error: " . $product->error());
					$page->addError($product->error());
				} else {
					header("location: /_build/product?id=".$product->id);
					return;
				}
			}
		} catch (Exception $e) {
			$page->addError("An error occurred, please try again");
		}
	}
