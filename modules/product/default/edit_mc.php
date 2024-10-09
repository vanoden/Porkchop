<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage products');

	// Valid Item Types
	$item_types = array( "inventory", "unique", "group", "kit", "note" );

	// Fetch Code from Query String if not Posted
	$new_item = false;
	if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
		$item = new \Product\Item($_REQUEST['id']);
	}
	elseif (!empty($_REQUEST['code'])) {
		$item = new \Product\Item();
		if ($item->validCode($_REQUEST['code'])) {
			$item->get($_REQUEST['code']);
			if ($item->id) $new_item = false;
			else $new_item = true;
		}
		else $page->addError("Invalid product code");
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item = new \Product\Item();
		if ($item->validCode($GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
			if ($item->id) $new_item = false;
			else $new_item = true;
		}
		else $page->addError("Invalid product code");
	}
	else {
		$item = new \Product\Item();
		$new_item = true;
	}
	
	if (! $new_item && ! $item->id) $page->addError("Item not found");
    	
	// Handle Actions
	elseif (isset($_REQUEST['updateSubmit'])) {
	    // CSRF Token Check
	    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) $page->addError("Invalid Request");
	    if (!$page->errorCount()) {
		    if (empty($_REQUEST['code']))
			    $page->addError("Code required");
			elseif (! $item->validCode($_REQUEST['code']))
				$page->addError("Invalid code");
			elseif (empty($_REQUEST['status']))
			    $page->addError("Status required");
			elseif (! $item->validStatus($_REQUEST['status']))
				$page->addError("Invalid status");
			elseif (empty($_REQUEST['type']))
			    $page->addError("Type required");
			elseif (! $item->validType($_REQUEST['type']))
				$page->addError("Invalid type");
		    else {
			    if ($new_item) {
				    app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." editing product ".$_REQUEST['code'],'notice',__FILE__,__LINE__);
				    $item->add(array(
					    "code"	=> $_REQUEST['code'],
					    "type"	=> $_REQUEST['type'],
					    "status"	=> $_REQUEST['status']
				    ));
			    } else app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." adding product ".$_REQUEST['code'],'notice',__FILE__,__LINE__);

			    $item->update(
				    array(
					    "code"		=> $_REQUEST['code'],
					    "type"		=> $_REQUEST['type'],
					    "status"	=> $_REQUEST["status"]
				    )
			    );
			    if ($item->error()) {
				    app_log("Error updating item: ".$item->error(),'error',__FILE__,__LINE__);
				    $page->addError("Error updating Item");
			    }

			    # Associate with a Parent if one selected or new product
			    if (!empty($_REQUEST['parent_code'])) {
				    $parent = new \Product\Item();
				    $parent->get($_REQUEST['parent_code']);
				    if ($parent->error()) {
					    app_log("Error finding item ".$_REQUEST['parent_code'],'error',__FILE__,__LINE__);
					    $page->addError("Error finding parent");
				    } elseif ($parent->id) {
					    $relationship = new \Product\Relationship();
					    $relationship->add(array(
						    "parent_id"	=> $parent->id,
						    "child_id" => $item->id
					    ));
				    }
			    } elseif ($new_item) {
				    $relationship = new \Product\Relationship();
				    $relationship->add(array(
					    "parent_id"	=> 0,
					    "child_id" => $item->id
				    ));
			    }

			    // Add Company Specific Metadata (REPLACE WITH CONFIGURED LOOP OF KEYS)
				$meta_fields = array("name","short_description","description","model","emperical_formula","sensitivity","measure_range","accuracy","manual_id","datalogger","spec_table_image","default_dashboard_id");
				foreach ($meta_fields as $meta_field) {
					if (isset($_REQUEST[$meta_field])) {
						$metadata = $item->metadata();
						$metadata->get($meta_field);
						$value = trim($_REQUEST[$meta_field]);
						if ($metadata->value != $value) {
							$metadata->set($value);
							if ($metadata->error()) $page->addError("Error setting ".$meta_field.": ".$metadata->error());
							else $page->appendSuccess("Updated '".$meta_field."'");
						}
					}
				}

			    $image = new \Media\Image();
			    if ($_REQUEST['new_image_code']) {
				    $image->get($_REQUEST['new_image_code']);
				    $item->addImage($image->id);
			    }
			    
			    if ($_REQUEST['deleteImage']) {
				    $image->get($_REQUEST['deleteImage']);
				    $item->dropImage($image->id);
			    }
		    }

			if (is_numeric($_REQUEST['new_price_amount']) && $_REQUEST['new_price_amount'] > 0) {
				$price = new \Product\Price();
				if (! $price->validStatus($_REQUEST['new_price_status'])) {
					$page->addError("Invalid price status");
				} elseif (!get_mysql_date($_REQUEST['new_price_date'])) {
					$page->addError("Invalid price active date: '".$_REQUEST['new_price_date']."'");
				} else {
					$newPrice = $item->addPrice(array('date_active' => $_REQUEST['new_price_date'], 'status' => $_REQUEST['new_price_status'], 'amount' => $_REQUEST['new_price_amount']));

					if ($item->error()) {
    					$page->addError($item->error());
					} else {
					
    					$page->success .= "Price Added";
    					
					    // audit the price change
					    $priceAudit = new \Product\PriceAudit();
					    $priceAudit->add (
                            array (
                                'product_price_id' => $newPrice->id,
                                'user_id' => $GLOBALS['_SESSION_']->customer->id,
                                'note' => "New Price Added by: " . $GLOBALS['_SESSION_']->customer->first_name . " " . $GLOBALS['_SESSION_']->customer->last_name . " for: $" .$_REQUEST['new_price_amount'] 
                            )
					    );
					    
					    // catch price audit error
					    if ($priceAudit->error()) $page->addError($priceAudit->error());
					}
				}
			}
		}
	}

	// add tag to product
	if (!empty($_REQUEST['addTag']) && empty($_REQUEST['removeTag'])) {
	    $productTag = new \Product\Tag();
	    if (!empty($_REQUEST['newTag']) && $productTag->validName($_REQUEST['newTag'])) {
	        $productTag->add(array('product_id'=>$item->id,'name'=>$_REQUEST['newTag']));
			if ($productTag->error()) {
				$page->addError("Error adding product tag: ".$productTag->error());
			} else {
				$page->appendSuccess("Product Tag added Successfully");
			}
	    } else {
    	    $page->addError("Value for Product Tag is required");
	    }
	}
	
	// remove tag from organization
	if (!empty($_REQUEST['removeTagId'])) {
        $productTagList = new \Product\TagList();
        $productTags = $productTagList->find(array("product_id" => $item->id, "id"=> $_REQUEST['removeTagId']));
	    foreach ($productTags as $productTag) {
			$productTag->delete();
			$page->appendSuccess("Product Tag removed Successfully");
		}
	}

	// get tags for product
	$productTagList = new \Product\TagList();
	$productTags = $productTagList->find(array("product_id" => $item->id));

	// add tag to product
	if (!empty($_REQUEST['newSearchTag']) && empty($_REQUEST['removeSearchTag'])) {
		$searchTag = new \Site\SearchTag();
		$searchTagList = new \Site\SearchTagList();
		$searchTagXref = new \Site\SearchTagXref();

		if (!empty($_REQUEST['newSearchTag']) && !empty($_REQUEST['newSearchTagCategory']) && $searchTag->validName($_REQUEST['newSearchTag']) && $searchTag->validName($_REQUEST['newSearchTagCategory'])) {

			// Check if the tag already exists
			$existingTag = $searchTagList->findAdvanced(array('class' => 'Product::Item', 'value' => $_REQUEST['newSearchTag']));

			if (empty($existingTag)) {

				// Create a new tag
				$searchTag->add(array('class' => 'Product::Item', 'category' => $_REQUEST['newSearchTagCategory'], 'value' => $_REQUEST['newSearchTag']));
				if ($searchTag->error()) {
					$page->addError("Error adding product search tag");
				} else {
					// Create a new cross-reference
					$searchTagXref->add(array('tag_id' => $searchTag->id, 'object_id' => $item->id));
					if ($searchTagXref->error()) {
						$page->addError("Error adding product tag cross-reference: " . $searchTagXref->error());
					} else {
						$page->appendSuccess("Product Search Tag added Successfully");
					}
				}
			} else {
				// Create a new cross-reference with the existing tag
				$searchTagXref->add(array('tag_id' => $existingTag[0]->id, 'object_id' => $item->id));
				if ($searchTagXref->error()) {
					$page->addError("Error adding product tag cross-reference: " . $searchTagXref->error());
				} else {
					$page->appendSuccess("Product Search Tag added Successfully");
				}
			}
		} else {
			$page->addError("Value for Product Tag and Category are required");
		}
	}

	// remove tag from product
	if (!empty($_REQUEST['removeSearchTagId'])) {
		$searchTagXrefItem = new \Site\SearchTagXref();
		$searchTagXrefItem->deleteTagForObject($_REQUEST['removeSearchTagId'], "Product::Item", $item->id);
		$page->appendSuccess("Product Search Tag removed Successfully");
	}

	// get tags for product
	$searchTagXref = new \Site\SearchTagXrefList();
	$searchTagXrefs = $searchTagXref->find(array("object_id" => $item->id, "class" => "Product::Item"));

	$productSearchTags = array();
	foreach ($searchTagXrefs as $searchTagXrefItem) {
		$searchTag = new \Site\SearchTag();
		$searchTag->load($searchTagXrefItem->tag_id);
		$productSearchTags[] = $searchTag;
	}

	// Get Product
	if (isset($_REQUEST['code'])) $item->get($_REQUEST['code']);
	if ($item->error()) $page->addError("Error loading item '".$_REQUEST['code']."': ".$item->error());

	// Get Manuals
	$documentlist = new \Media\DocumentList();
	$manuals = $documentlist->find();
	$imagelist = new \Media\ImageList();
	$tables = $imagelist->find();
	$dashboardlist = new \Monitor\DashboardList();
	$dashboards = $dashboardlist->find();
	$prices = $item->prices();
    $priceAudit = new \Product\PriceAuditList();
    $auditedPrices = $priceAudit->find(array('product_id'=>$item->id));
	$images = $item->images();

	$page->addBreadcrumb("Products", "/_product/report");
	if (isset($item->id)) $page->addBreadcrumb($item->code,"/_product/edit/".$item->code);

	// get unique categories and tags for autocomplete
	$searchTagList = new \Site\SearchTagList();
	$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();