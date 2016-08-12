<?
	require_once(MODULES."/product/_classes/default.php");
	require_once(MODULES."/media/_classes/default.php");

	# Check Permissions
	if (! in_array("product manager",$GLOBALS['_SESSION_']->customer->roles))
	{
		header("location: /_product/browse");
		exit;
	}
	
	# Initialize Class
	$_item = new Product();

	# Fetch Code from Query String if not Posted
	if (! $_REQUEST['code'])
		$_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];

	# Get Product
	$item = $_item->get($_REQUEST['code']);

	# Handle Actions
	if ($_REQUEST['submit'] == "Update")
	{
		if (! $_REQUEST['code'])
		{
			$GLOBALS['_page']->error = "Code required";
		}
		else
		{
			app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." editing product ".$_REQUEST['code'],'notice',__FILE__,__LINE__);
			$item = $_item->update(
				$item->id,
				array(
					"status"			=> $_REQUEST["status"]
				)
			);
			if ($_item->error)
			{
				app_log("Error updating item: ".$_item->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = "Error updating Item";
			}
			$_item->addMeta($item->id,"name",$_REQUEST["name"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"short_description",$_REQUEST["short_description"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"description",$_REQUEST["description"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"model",$_REQUEST["model"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"empirical_formula",$_REQUEST["empirical_formula"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"sensitivity",$_REQUEST["sensitivity"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"measure_range",$_REQUEST["measure_range"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"accuracy",$_REQUEST["accuracy"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"manual_id",$_REQUEST["manual_id"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
			$_item->addMeta($item->id,"spec_table_image",$_REQUEST["spec_table_image"]);
			if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);

			$_image = new MediaImage();
			if ($_REQUEST['new_image_code'])
			{
				$image = $_image->get($_REQUEST['new_image_code']);
				$_item->addImage($item->id,$image->id);
			}
			if ($_REQUEST['deleteImage'])
			{
				$image = $_image->get($_REQUEST['deleteImage']);
				$_item->dropImage($item->id,$image->id);
			}
		}
	}
	elseif ($_REQUEST['submit'] == "Add")
	{
		if (! $_REQUEST['code'])
		{
			$GLOBALS['_page']->error = "Code required";
		}
		elseif (! $_REQUEST['status'])
		{
			$GLOBALS['_page']->error = "Status required";
		}
		elseif (! $_REQUEST['type'])
		{
			$GLOBALS['_page']->error = "Type required";
		}
		elseif ($item->id)
		{
			$GLOBALS['_page']->error = "Product with code already exists";
		}
		else
		{
			app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." adding product ".$_REQUEST['code'],'notice',__FILE__,__LINE__);
			$item = $_item->add(
				array(
					"code"		=> $_REQUEST["code"],
					"status"	=> $_REQUEST["status"],
					"type"		=> $_REQUEST["type"],
				)
			);
			if ($_item->error)
			{
				app_log("Error adding item: ".$_item->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = "Error adding Item";
			}
			else
			{
				$parent = $_item->get($_REQUEST['parent_code']);
				if ($_item->error)
				{
					app_log("Error finding item ".$_REQUEST['parent_code'],'error',__FILE__,__LINE__);
					$GLOBALS['_page']->error = "Error finding parent";
				}
				elseif ($parent->id)
				{
					$_relationship = new ProductRelationship();
					$_relationship->add(array(
						"parent_id"	=> $parent->id,
						"child_id" => $item->id
					));
				}
				else
				{
					$_relationship = new ProductRelationship();
					$_relationship->add(array(
						"parent_id"	=> 0,
						"child_id" => $item->id
					));
				}
				$_item->addMeta($item->id,"name",$_REQUEST["name"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"short_description",$_REQUEST["short_description"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"description",$_REQUEST["description"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"model",$_REQUEST["model"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"empirical_formula",$_REQUEST["empirical_formula"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"sensitivity",$_REQUEST["sensitivity"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"measure_range",$_REQUEST["measure_range"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"accuracy",$_REQUEST["accuracy"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"manual_id",$_REQUEST["manual_id"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);
				$_item->addMeta($item->id,"spec_table_image",$_REQUEST["spec_table_image"]);
				if ($_item->error) app_log("Error setting metadata: ".$_item->error,'error',__FILE__,__LINE__);

				if ($_REQUEST['new_image_code'])
				{
					$_image = new MediaImage();
					$image = $_image->get($_REQUEST['new_image_code']);
					$_item->addImage($item->id,$image->id);
				}
			}
		}
	}
	elseif($_REQUEST['submit'])
	{
		$GLOBALS['_page']->error = "Invalid request";
	}

	# Get Product
	$item = $_item->get($_REQUEST['code']);
	if ($_item->error)
	{
		$GLOBALS['_page']->error = "Error loading item '".$_REQUEST['code']."': ".$_item->error;
	}
	
	# Get Manuals
	$_document = new MediaDocument();
	$manuals = $_document->find();
	$_image = new MediaImage();
	$tables = $_image->find();
?>
