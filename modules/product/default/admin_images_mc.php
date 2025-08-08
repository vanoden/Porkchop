<?php

	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

	// Initialize validation objects
	$item = new \Spectros\Product\Item();

	// Get Image Repository
	$repository = new \Storage\Repository();
	$site_config = new \Site\Configuration();
	$site_config->get('website_images');
	if (!empty($site_config->value)) $repository->get($site_config->value);
	$repository = $repository->getInstance();

	// Validate item by ID
	if ($item->validInteger($_REQUEST['id'] ?? null)) {
		$item = new \Spectros\Product\Item($_REQUEST['id']);
		if (!$item->id) {
			$page->addError("Item not found");
		}
	}
	// Validate item by code
	elseif ($item->validCode($_REQUEST['code'] ?? null)) {
		$item->get($_REQUEST['code']);
	}
	// Validate item by query vars
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && $item->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	else {
		$page->notFound();
	}

	if ($item->id) {
		// File Upload Form Submitted
		if (isset($_REQUEST['btn_submit']) && $_REQUEST['btn_submit'] == 'Upload') {
			if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) $page->addError("Invalid Token");
			else {
				$page->requirePrivilege('upload storage files');
				// Get the Repository ID for Product Images
				$configuration = new \Site\Configuration();
				$configuration->get('website_images');
				if (empty($configuration->value)) {
					$page->addError("No repository configured for product images");
					print_r("No repository configured for product images");
				}
				elseif (!$repository->get($configuration->value)) {
					$page->addError("Repository not found for product images");
					print_r("Repository not found for product images");
				}
				else {
					$imageUploaded = $item->uploadImage($_FILES['uploadFile'], $repository->id, 'spectros_product_image', 'Product\Item');
					if ($imageUploaded) $page->success = "File uploaded";
					else $page->addError("Error uploading file: " . $item->error());
				}
			}
		}

		// Update Default Image
		if (isset($_REQUEST['updateImage']) && $_REQUEST['updateImage'] == 'true') {
			if ($item->validInteger($_REQUEST['default_image_id'] ?? null)) {
				$defaultImageId = $_REQUEST['default_image_id'];
				$item->setMetadataScalar('default_image', $defaultImageId);
				if ($item->error()) $page->addError("Error setting default image: " . $item->error());
				else $page->appendSuccess('Default image updated successfully.', 'success');
			} else {
				$page->addError("Invalid image ID");
			}
		}
	
		// Check if item has images
		$images = $item->images();
		$defaultImageId = $item->getMetadata('default_image');
	}


	$page->addBreadcrumb('Products', '/_product/admin');
	$page->addBreadcrumb($item->code, '/_spectros/admin_product/'.$item->code);

	$page->title("Product Images");
?>
