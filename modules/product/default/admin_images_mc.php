<?php

	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage products');

	// Initialize validation objects
	$item = new \Product\Item();

	// Get Image Repository
	$repository = new \Storage\Repository();
	$site_config = new \Site\Configuration();
	$site_config->get('website_images');
	if (!empty($site_config->value)) $repository->get($site_config->value);
	$repository = $repository->getInstance();

	// Validate item by ID
	if ($item->validInteger($_REQUEST['id'] ?? null)) {
		$item = new \Product\Item($_REQUEST['id']);
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
				}
				elseif (!$repository->get($configuration->value)) {
					$page->addError("Repository not found for product images");
				}
				elseif (isset($_FILES['uploadFile']['error']) && $_FILES['uploadFile']['error'] > 0) {
					switch($_FILES['uploadFile']['error']) {
						case 1:
							$page->addError("The upload file exeeds the server maximum size");
							break;
						case 2:
							$page->addError("The upload file exeeds the form maximum size");
							break;
						case 3:
							$page->addError("The file was only partially uploaded");
							break;
						case 4:
							$page->addError("No file was uploaded");
							break;
						case 5:
							$page->addError("Upload folder missing");
							break;
						case 6:
							$page->addError("Failed to write file to disk");
							break;
						case 7:
							$page->addError("File upload was blocked by server");
							break;
						default:
							$page->addError("Unknown error with file upload");
							break;
					}
					app_log("File upload error: ".print_r($_FILES['uploadFile'],true),"notice");
				}
				else {
					// Use the actual class name for consistency
					$imageUploaded = $item->uploadImage($_FILES['uploadFile'], $repository->id, 'spectros_product_image', get_class($item));
					if ($imageUploaded) $page->success = "File uploaded";
					else $page->addError("Error uploading file: " . $item->error());
				}
			}
		}

		// Delete Image from Product
		if (!empty($_REQUEST['deleteImage'])) {
			if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'] ?? '')) {
				$page->addError("Invalid Token");
			} else {
				$imageId = $_REQUEST['deleteImage'];
				if ($item->validInteger($imageId)) {
					// Verify the image exists and is associated with this product
					$image = new \Storage\File($imageId);
					if ($image->id) {
						// Try both object_type values in case there's a mismatch
						$result = $item->dropImage($imageId, get_class($item));
						if (!$result) {
							// Try with 'Product\Item' for legacy images
							$result = $item->dropImage($imageId, 'Product\Item');
						}
						
						if ($item->error()) {
							$page->addError("Error removing image: " . $item->error());
						} elseif ($result) {
							$page->appendSuccess('Image removed from product successfully.');
							// Also remove from default if it was the default image
							$currentDefaultId = $item->getMetadata('default_image');
							if ($currentDefaultId == $imageId) {
								$item->unsetMetadata('default_image');
							}
							// Reload item to ensure fresh data
							if ($item->code) {
								$item->get($item->code);
							}
						} else {
							$page->addError("Failed to remove image association");
						}
					} else {
						$page->addError("Image not found");
					}
				} else {
					$page->addError("Invalid image ID format");
				}
			}
		}

		// Update Default Image
		if (isset($_REQUEST['updateImage']) && $_REQUEST['updateImage'] == 'true') {
			// Handle removal of default image (empty value)
			if (empty($_REQUEST['default_image_id'])) {
				$item->unsetMetadata('default_image');
				if ($item->error()) $page->addError("Error removing default image: " . $item->error());
				else $page->appendSuccess('Default image removed successfully.', 'success');
			}
			// Handle setting a new default image
			elseif ($item->validInteger($_REQUEST['default_image_id'] ?? null)) {
				$defaultImageId = $_REQUEST['default_image_id'];
				$item->setMetadataScalar('default_image', $defaultImageId);
				if ($item->error()) $page->addError("Error setting default image: " . $item->error());
				else $page->appendSuccess('Default image updated successfully.', 'success');
			} else {
				$page->addError("Invalid image ID");
			}
		}

		// Check if item has images - try both object_type values to handle legacy data
		$images = $item->images(); // Use default (actual class name)
		if (empty($images)) {
			// Fallback to 'Product\Item' for legacy images
			$images = $item->images('Product\Item');
		}
		$defaultImageId = $item->getMetadata('default_image');
	}

	// Set Breadcrumbs and Title
	$page->addBreadcrumb('Products', '/_spectros/admin_products');
	$page->addBreadcrumb($item->code, '/_spectros/admin_product/'.$item->code);

	$page->title("Product Images");
?>
