<?php
	$site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage customers');
	
	if (!empty($_REQUEST['id']) && empty($_REQUEST['organization_id'])) $_REQUEST['organization_id'] = $_REQUEST['id'];

	# Security - Only Register Module Operators or Managers can see other customers
	if ($GLOBALS['_SESSION_']->customer->can('manage customers')) {
		if (isset($_REQUEST['organization_id']) && preg_match('/^\d+$/',$_REQUEST['organization_id'])) {
			$organization = new \Register\Organization($_REQUEST['organization_id']);
			if ($organization->error()) $page->addError("Unable to load organization: ".$organization->error());
		}
		elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0]) && preg_match('/^[\w\-\.\_]+$/',$GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$code = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$organization = new \Register\Organization();
			if ($organization->validCode($code)) {
				$organization->get($code);
				if (! $organization->id) $page->addError("Organization not found");
			}
			else {
				$page->addError("Invalid organization code");
			}
		}
		else $organization = new \Register\Organization();
	}
	else $organization = $GLOBALS['_SESSION_']->customer->organization();

	// add tag to organization
	if (!empty($_REQUEST['addTag']) && empty($_REQUEST['removeTag'])) {
	    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	        $page->addError("Invalid Request");
	    }
	    else {
	        $registerTag = new \Register\Tag();
	        if (!empty($_REQUEST['newTag']) && $registerTag->validName($_REQUEST['newTag'])) {
	            $registerTag->add(array('type'=>'ORGANIZATION','register_id'=>$_REQUEST['organization_id'],'name'=>$_REQUEST['newTag']));
	            if ($registerTag->error()) {
	                $page->addError("Error adding organization tag: ".$registerTag->error());
	            }
	            else {
	                $page->appendSuccess("Organization Tag added Successfully");
	            }
	        }
	        else {
	            $page->addError("Value for Organization Tag is required");
	        }
	    }
	}
	
	// remove tag from organization
	if (!empty($_REQUEST['removeTagId'])) {
	    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	        $page->addError("Invalid Request");
	    }
	    else {
	        $registerTagList = new \Register\TagList();
	        $organizationTags = $registerTagList->find(array("type" => "ORGANIZATION", "register_id" => $organization->id, "id"=> $_REQUEST['removeTagId']));
	        foreach ($organizationTags as $organizationTag) $organizationTag->delete();
	        $page->appendSuccess("Tag removed successfully");
	    }
	}

	// get tags for organization (after any add/remove operations)
	if ($organization->id) {
		$registerTagList = new \Register\TagList();
		$organizationTags = $registerTagList->find(array("type" => "ORGANIZATION", "register_id" => $organization->id));
	}

	$page->title = "Organization Tags";
