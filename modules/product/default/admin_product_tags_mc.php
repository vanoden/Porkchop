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

	// Initialize productSearchTags array in case item is not found
	$productSearchTags = array();

	if ($item->id) {
		// get tags for product (using BaseModel unified tag system)
		$productTags = $item->getTags();
		if (!is_array($productTags)) {
			$productTags = array();
		}

		// add search tag or product tag to product (using BaseModel unified tag system)
		if (!empty($_REQUEST['newSearchTag']) && empty($_REQUEST['removeSearchTag'])) {
			if (!$item->validTagValue($_REQUEST['newSearchTag'])) {
				$page->addError("Invalid tag format. Tags can only contain letters, numbers, dashes, underscores, dots, and spaces.");
			} else {
				// If category is empty (product tag), default to product_tag; otherwise use provided category (search tag)
				$category = trim($_REQUEST['newSearchTagCategory'] ?? '');
				if ($category === '') {
					$category = 'product_tag';
				}
				if (!$item->validTagCategory($category)) {
					$page->addError("Invalid tag category format.");
				} elseif ($item->addTag($_REQUEST['newSearchTag'], $category)) {
					$page->appendSuccess($category === 'product_tag' ? "Product Tag added Successfully" : "Product Search Tag added Successfully");
				} else {
					$page->addError("Error adding tag: " . $item->error());
				}
			}
		}

		// remove search tag from product (using BaseModel unified tag system)
		if (!empty($_REQUEST['removeSearchTagId'])) {
			$searchTagXrefItem = new \Site\SearchTagXref();
			if ($searchTagXrefItem->validInteger($_REQUEST['removeSearchTagId'])) {
				$searchTagXrefItem = new \Site\SearchTagXref($_REQUEST['removeSearchTagId']);
				if ($searchTagXrefItem->id) {
					$searchTag = new \Site\SearchTag($searchTagXrefItem->tag_id);
					$tagClass = $item->getTagClass(); // Get the normalized class name
					if ($searchTag->id && $searchTag->class === $tagClass) {
						if ($item->removeTag($searchTag->value, $searchTag->category)) {
							$page->appendSuccess("Product Search Tag removed Successfully");
						} else {
							$page->addError("Error removing product search tag: " . $item->error());
						}
					}
				}
			} else {
				$page->addError("Invalid tag ID format");
			}
		}

		// get search tags for product (using BaseModel unified tag system)
		$tagClass = $item->getTagClass(); // Get the normalized class name
		$searchTagXref = new \Site\SearchTagXrefList();
		$searchTagXrefs = $searchTagXref->find(array("object_id" => $item->id, "class" => $tagClass));
		foreach ($searchTagXrefs as $searchTagXrefItem) {
			$searchTag = new \Site\SearchTag();
			$searchTag->load($searchTagXrefItem->tag_id);
			$productSearchTags[] = (object) array('searchTag' => $searchTag, 'xrefId' => $searchTagXrefItem->id);
		}
		// get unique categories and tags for autocomplete
		$searchTagList = new \Site\SearchTagList();
		$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();

		// get tags for organization
	    $registerTagList = new \Register\TagList();
	    $org = isset($organization) && is_object($organization) ? $organization : $GLOBALS['_SESSION_']->customer->organization();
	    if ($org && $org->id) {
	    	$organizationTags = $registerTagList->find(array("type" => "ORGANIZATION", "register_id" => $org->id));
	    } else {
	    	$organizationTags = array();
	    }
	}

	if (!isset($uniqueTagsData) || !is_array($uniqueTagsData)) {
		$searchTagList = new \Site\SearchTagList();
		$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();
	}

	$page->addBreadcrumb('Products', '/_spectros/admin_products');
	$page->addBreadcrumb($item->code, '/_spectros/admin_product/'.$item->code);
	$page->title("Product Tags");
