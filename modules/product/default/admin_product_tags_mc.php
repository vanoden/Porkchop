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

	// Initialize productSearchTags array in case item is not found
	$productSearchTags = array();

	if ($item->id) {
		// get tags for product
		$productTagList = new \Product\TagList();
		$productTags = $productTagList->find(array("product_id" => $item->id));

		// add tag to product
		if (!empty($_REQUEST['newSearchTag']) && empty($_REQUEST['removeSearchTag'])) {
			$searchTag = new \Site\SearchTag();
			$searchTagList = new \Site\SearchTagList();
			$searchTagXref = new \Site\SearchTagXref();

			if (!empty($_REQUEST['newSearchTag']) && !empty($_REQUEST['newSearchTagCategory']) && 
				$searchTag->validName($_REQUEST['newSearchTag']) && 
				$searchTag->validName($_REQUEST['newSearchTagCategory'])) {

				// Check if the tag already exists
				$existingTag = $searchTagList->find(array('class' => 'Product::Item', 'value' => $_REQUEST['newSearchTag']));

				if (empty($existingTag)) {
					// Create a new tag
					$searchTag->add(array(
						'class' => 'Product::Item', 
						'category' => $_REQUEST['newSearchTagCategory'], 
						'value' => $_REQUEST['newSearchTag']
					));
					
					if ($searchTag->error()) $page->addError("Error adding product search tag: " . $searchTag->error());
					else {
						// Create a new cross-reference
						$searchTagXref->add(array('tag_id' => $searchTag->id, 'object_id' => $item->id));
						if ($searchTagXref->error()) $page->addError("Error adding product tag cross-reference: " . $searchTagXref->error());
						else $page->appendSuccess("Product Search Tag added Successfully");
					}
				} else {
					// Create a new cross-reference with the existing tag
					$searchTagXref->add(array('tag_id' => $existingTag[0]->id, 'object_id' => $item->id));
					if ($searchTagXref->error()) $page->addError("Error adding product tag cross-reference: " . $searchTagXref->error());
					else $page->appendSuccess("Product Search Tag added Successfully");
				}
			} else $page->addError("Value for Product Tag and Category are required");
		}

		// remove tag from product
		if (!empty($_REQUEST['removeSearchTagId'])) {
			$searchTagXrefItem = new \Site\SearchTagXref();
			if ($searchTagXrefItem->validInteger($_REQUEST['removeSearchTagId'])) {
				$searchTagXrefItem->deleteTagForObject($_REQUEST['removeSearchTagId'], "Product::Item", $item->id);
				$page->appendSuccess("Product Search Tag removed Successfully");
			} else {
				$page->addError("Invalid tag ID format");
			}
		}

		// get tags for product
		$searchTagXref = new \Site\SearchTagXrefList();
		$searchTagXrefs = $searchTagXref->find(array("object_id" => $item->id, "class" => "Product::Item"));
		foreach ($searchTagXrefs as $searchTagXrefItem) {
			$searchTag = new \Site\SearchTag();
			$searchTag->load($searchTagXrefItem->tag_id);
			$productSearchTags[] = $searchTag;
		}
		// get unique categories and tags for autocomplete
		$searchTagList = new \Site\SearchTagList();
		$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();

		// get tags for organization
	    $registerTagList = new \Register\TagList();
	    $organizationTags = $registerTagList->find(array("type" => "ORGANIZATION", "register_id" => $organization->id));
	}

	$page->addBreadcrumb('Products', '/_product/admin');
	$page->addBreadcrumb($item->code, '/_spectros/admin_product/'.$item->code);
	$page->title("Product Tags");