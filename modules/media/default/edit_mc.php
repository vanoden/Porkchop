<?php

// Return 404 to exclude from testing for now
header("HTTP/1.0 404 Not Found");
exit;

	require_once(MODULES."/media/_classes/default.php");

	$can_proceed = true;

	# Check Permissions
	if (! in_array("media manager",$GLOBALS['_SESSION_']->customer->roles)) {
		header("location: /_media/browse");
		exit;
	}
	
	# Initialize Class
	$_item = new MediaItem();

	# Fetch Code from Query String if not Posted
	$code = $_REQUEST['code'] ?? $GLOBALS['_REQUEST_']->query_vars_array[0] ?? '';

	if (empty($code)) {
		$GLOBALS['_page']->addError("Media code is required");
		$can_proceed = false;
	} elseif (!$_item->validText($code)) {
		$GLOBALS['_page']->addError("Invalid media code format");
		$can_proceed = false;
	}

	# Get Media Item if code is valid
	$item = null;
	if ($can_proceed) {
		$item = $_item->get($code);
		if (!$item || $_item->error) {
			$GLOBALS['_page']->addError("Media item not found");
			$can_proceed = false;
		}
	}

	# Handle Actions
	$submit = $_REQUEST['submit'] ?? null;
	if (!empty($submit) && $can_proceed) {
		app_log("Admin ".$GLOBALS['_SESSION_']->customer->first_name." editing media item ".$code,'notice',__FILE__,__LINE__);
		
		$name = $_REQUEST['name'] ?? '';
		$icon = $_REQUEST['icon'] ?? '';
		$description = $_REQUEST['description'] ?? '';
		
		if (empty($name)) {
			$GLOBALS['_page']->addError("Name is required");
			$can_proceed = false;
		} elseif (!$_item->validText($name)) {
			$GLOBALS['_page']->addError("Invalid name format");
			$can_proceed = false;
		}
		
		if (!empty($icon) && !$_item->validText($icon)) {
			$GLOBALS['_page']->addError("Invalid icon format");
			$can_proceed = false;
		}
		
		if (!empty($description) && !$_item->validText($description)) {
			$GLOBALS['_page']->addError("Invalid description format");
			$can_proceed = false;
		}
		
		if ($can_proceed) {
			foreach(array('name','icon','description') as $key) {
				$value = $_REQUEST[$key] ?? '';
				$_item->setMeta(
					$item->id,
					$key,
					$value
				);
				if ($_item->error) {
					app_log("Error updating item: ".$_item->error,'error',__FILE__,__LINE__);
					$GLOBALS['_page']->addError("Error updating Item");
					$can_proceed = false;
					break;
				}
			}
			
			if ($can_proceed) {
				$item = $_item->get($code);
				$GLOBALS['_page']->appendSuccess("Media item updated successfully");
			}
		}
	}
