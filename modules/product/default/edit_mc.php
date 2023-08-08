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
	elseif (isset($_REQUEST['submit'])) {
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
				$meta_fields = array("name","short_description","description","model","emperical_formula","sensitivity","measure_range","accuracy","manual_id","spec_table_image","default_dashboard_id");
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
                                'note' => "New Price Added by: " . $GLOBALS['_SESSION_']->customer->first_name . " " . $GLOBALS['_SESSION_']->customer->last_name
                            )
					    );
					    
					    // catch price audit error
					    if ($priceAudit->error()) $page->addError($priceAudit->error());
					}
				}
			}
		}
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
