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

	// add tag to organization (using BaseModel unified tag system) — category organization_tag
	if (!empty($_REQUEST['addTag']) && empty($_REQUEST['removeTag'])) {
	    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	        $page->addError("Invalid Request");
	    }
	    elseif (empty($organization->id)) {
	        $page->addError("Organization not found. Cannot add tag.");
	    }
	    else {
	        if (!empty($_REQUEST['newTag']) && $organization->validTagValue($_REQUEST['newTag'])) {
	            if ($organization->addTag($_REQUEST['newTag'], 'organization_tag')) {
	                $page->appendSuccess("Organization Tag added Successfully");
	            } else {
	                $page->addError("Error adding organization tag: ".$organization->error());
	            }
	        }
	        else {
	            $page->addError("Value for Organization Tag is required");
	        }
	    }
	}

	// remove tag from organization (using BaseModel unified tag system)
	if (!empty($_REQUEST['removeTagId'])) {
	    if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
	        $page->addError("Invalid Request");
	    }
	    elseif (!empty($organization->id)) {
	        // Get tag details from xref ID
	        $searchTagXrefItem = new \Site\SearchTagXref($_REQUEST['removeTagId']);
	        if ($searchTagXrefItem->id) {
	            $searchTag = new \Site\SearchTag($searchTagXrefItem->tag_id);
	            $tagClass = $organization->getTagClass();
	            $isOrgTag = ($searchTag->id && $searchTag->class === $tagClass && ($searchTag->category === 'organization_tag' || $searchTag->category === 'ORGANIZATION'));
	            if ($isOrgTag && $organization->removeTag($searchTag->value, $searchTag->category)) {
	                $page->appendSuccess("Tag removed successfully");
	            } elseif ($isOrgTag) {
	                $page->addError("Error removing tag: " . $organization->error());
	            }
	        }
	    }
	}

	// get tags for organization (using BaseModel unified tag system) — list with xref ids for remove
	$organizationTags = array();
	if (!empty($organization->id)) {
	    $tagClass = $organization->getTagClass();
	    $searchTagXref = new \Site\SearchTagXrefList();
	    $searchTagXrefs = $searchTagXref->find(array("object_id" => $organization->id, "class" => $tagClass));
	    foreach ($searchTagXrefs as $searchTagXrefItem) {
	        $searchTag = new \Site\SearchTag();
	        $searchTag->load($searchTagXrefItem->tag_id);
	        if ($searchTag->id && ($searchTag->category === 'organization_tag' || $searchTag->category === 'ORGANIZATION')) {
	            $organizationTags[] = (object) array('xrefId' => $searchTagXrefItem->id, 'name' => $searchTag->value);
	        }
	    }
	}

	$page->title = "Organization Tags";
	$page->setAdminMenuSection("Customer");  // Keep Customer section open
	$page->addBreadcrumb("Customer");
	$page->addBreadcrumb("Organizations", "/_register/admin_organizations");
	if (isset($organization->id)) {
		$page->addBreadcrumb($organization->name, "/_register/admin_organization?organization_id=".$organization->id);
	}
	$page->addBreadcrumb("Tags");
