<?php
	$page = new \Site\Page();
	$page->requirePrivilege('manage products');

	// Valid Item Types
	$item_types = array(
		"inventory",
		"unique",
		"group",
		"kit",
		"note"
	);

	// Fetch Code from Query String if not Posted
	$new_item = false;
	if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
		$item = new \Product\Item($_REQUEST['id']);
	} elseif (isset($_REQUEST['code']) && $_REQUEST['code']) {
		$item = new \Product\Item();
		$item->get($_REQUEST['code']);
		if ($item->id) $new_item = false;
		else $new_item = true;
	} elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$item = new \Product\Item();
		$item->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
		if ($item->id) $new_item = false;
		else $new_item = true;
	} else {
		$new_item = true;
	}

	if (! $new_item && ! $item->id) $page->addError("Item not found");
	
	// Handle Actions
	elseif (isset($_REQUEST['submit'])) {
		if (! isset($_REQUEST['code'])) {
			$page->addError("Code required");
		} elseif (! isset($_REQUEST['status'])) {
			$page->addError("Status required");
		} elseif (! isset($_REQUEST['type'])) {
			$page->addError("Type required");
		} else {
			if ($new_item) {
				app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." editing product ".$_REQUEST['code'],'notice',__FILE__,__LINE__);
				$item->add(array(
					"code"	=> $_REQUEST['code'],
					"type"	=> $_REQUEST['type'],
					"status"	=> $_REQUEST['status']
				));
			}
			else
				app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." adding product ".$_REQUEST['code'],'notice',__FILE__,__LINE__);

			$item->update(
				array(
					"code"	=> $_REQUEST['code'],
					"type"	=> $_REQUEST['type'],
					"status"			=> $_REQUEST["status"]
				)
			);
			if ($item->error) {
				app_log("Error updating item: ".$item->error,'error',__FILE__,__LINE__);
				$page->addError("Error updating Item");
			}

			# Associate with a Parent if one selected or new product
			if ($_REQUEST['parent_code']) {
				$parent = new \Product\Item();
				$parent->get($_REQUEST['parent_code']);
				if ($parent->error) {
					app_log("Error finding item ".$_REQUEST['parent_code'],'error',__FILE__,__LINE__);
					$page->addError("Error finding parent");
				}
				elseif ($parent->id) {
					$relationship = new \Product\Relationship();
					$relationship->add(array(
						"parent_id"	=> $parent->id,
						"child_id" => $item->id
					));
				}
			}
			elseif ($new_item) {
				$relationship = new \Product\Relationship();
				$relationship->add(array(
					"parent_id"	=> 0,
					"child_id" => $item->id
				));
			}

			# Add Company Specific Metadata (REPLACE WITH CONFIGURED LOOP OF KEYS)
			$item->addMeta("name",noXSS($_REQUEST["name"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("short_description",noXSS($_REQUEST["short_description"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("description",noXSS($_REQUEST["description"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("model",noXSS($_REQUEST["model"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("empirical_formula",noXSS($_REQUEST["empirical_formula"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("sensitivity",noXSS($_REQUEST["sensitivity"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("measure_range",noXSS($_REQUEST["measure_range"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("accuracy",noXSS($_REQUEST["accuracy"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("manual_id",noXSS($_REQUEST["manual_id"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("spec_table_image",noXSS($_REQUEST["spec_table_image"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("default_dashboard_id",noXSS($_REQUEST["default_dashboard_id"]));
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);

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
	}

	// Get Product
	if (isset($_REQUEST['code'])) $item->get($_REQUEST['code']);
	if (isset($item->error)) $page->addError("Error loading item '".$_REQUEST['code']."': ".$item->error);
	
	// Get Manuals
	$documentlist = new \Media\DocumentList();
	$manuals = $documentlist->find();
	$imagelist = new \Media\ImageList();
	$tables = $imagelist->find();
	$dashboardlist = new \Monitor\DashboardList();
	$dashboards = $dashboardlist->find();
