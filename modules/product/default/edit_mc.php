<?php
	$page = new \Site\Page();
	$page->requireRole('product manager');

	# Initialize Class
	$item = new \Product\Item();

	# Fetch Code from Query String if not Posted
	if (! $_REQUEST['code'])
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];

	# Get Product
	$item->get($_REQUEST['code']);

	# Handle Actions
	if ($_REQUEST['submit'] == "Update") {
		if (! $_REQUEST['code']) {
			$page->addError("Code required");
		}
		else {
			app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." editing product ".$_REQUEST['code'],'notice',__FILE__,__LINE__);
			$item->update(
				array(
					"status"			=> $_REQUEST["status"]
				)
			);
			if ($item->error) {
				app_log("Error updating item: ".$item->error,'error',__FILE__,__LINE__);
				$page->addError("Error updating Item");
			}
			$item->addMeta("name",$_REQUEST["name"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("short_description",$_REQUEST["short_description"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("description",$_REQUEST["description"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("model",$_REQUEST["model"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("empirical_formula",$_REQUEST["empirical_formula"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("sensitivity",$_REQUEST["sensitivity"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("measure_range",$_REQUEST["measure_range"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("accuracy",$_REQUEST["accuracy"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("manual_id",$_REQUEST["manual_id"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("spec_table_image",$_REQUEST["spec_table_image"]);
			if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
			$item->addMeta("default_dashboard_id",$_REQUEST["default_dashboard_id"]);
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
	elseif ($_REQUEST['submit'] == "Add") {
		if (! $_REQUEST['code']) {
			$page->addError("Code required");
		}
		elseif (! $_REQUEST['status']) {
			$page->addError("Status required");
		}
		elseif (! $_REQUEST['type']) {
			$page->addError("Type required");
		}
		elseif ($item->id) {
			$page->addError("Product with code already exists");
		}
		else {
			app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." adding product ".$_REQUEST['code'],'notice',__FILE__,__LINE__);
			$item->add(
				array(
					"code"		=> $_REQUEST["code"],
					"status"	=> $_REQUEST["status"],
					"type"		=> $_REQUEST["type"],
				)
			);
			if ($item->error)
			{
				app_log("Error adding item: ".$item->error,'error',__FILE__,__LINE__);
				$page->addError("Error adding Item");
			}
			else
			{
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
				else
				{
					$relationship = new \Product\Relationship();
					$relationship->add(array(
						"parent_id"	=> 0,
						"child_id" => $item->id
					));
				}
				$item->addMeta("name",$_REQUEST["name"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("short_description",$_REQUEST["short_description"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("description",$_REQUEST["description"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("model",$_REQUEST["model"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("empirical_formula",$_REQUEST["empirical_formula"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("sensitivity",$_REQUEST["sensitivity"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("measure_range",$_REQUEST["measure_range"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("accuracy",$_REQUEST["accuracy"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("manual_id",$_REQUEST["manual_id"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("spec_table_image",$_REQUEST["spec_table_image"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);
				$item->addMeta("default_dashboard_id",$_REQUEST["default_dashboard_id"]);
				if ($item->error) app_log("Error setting metadata: ".$item->error,'error',__FILE__,__LINE__);

				if ($_REQUEST['new_image_code'])
				{
					$image = new \Media\Image();
					$image->get($_REQUEST['new_image_code']);
					$item->addImage($image->id);
				}
			}
		}
	}
	elseif($_REQUEST['submit']){
		$page->addError("Invalid request");
	}

	# Get Product
	$item->get($_REQUEST['code']);
	if ($item->error) {
		$page->addError("Error loading item '".$_REQUEST['code']."': ".$item->error);
	}
	
	# Get Manuals
	$documentlist = new \Media\DocumentList();
	$manuals = $documentlist->find();
	$imagelist = new \Media\ImageList();
	$tables = $imagelist->find();
	$dashboardlist = new \Monitor\DashboardList();
	$dashboards = $dashboardlist->find();
