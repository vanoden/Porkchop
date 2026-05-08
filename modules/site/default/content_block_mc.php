<?php
	$page = new \Site\Page();
	$page->requirePrivilege('edit content messages');

	if (isset($_REQUEST['id'])) {
		$message = new \Content\Message($_REQUEST['id']);
	}
	elseif (!empty($_REQUEST['method']) && $_REQUEST['method'] == 'new') {
		// New Page, Load Nothing
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$message = new \Content\Block();
		if (!$message->get($GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$message->name = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$message->target = $GLOBALS['_REQUEST_']->query_vars_array[0];
		}
	}
	elseif (!empty($GLOBALS['_REQUEST_']->index)) {
		$message = new \Content\Block();
		if (!$message->get($GLOBALS['_REQUEST_']->index)) {
			$message->name = $GLOBALS['_REQUEST_']->index;
			$message->target = $GLOBALS['_REQUEST_']->index;
		}
	}
	else {
		// Home Page
		$message = new \Content\Block();
		$message->get('');
	}
	$show_add_page = false;

	// handle page submit
	if (isset($_REQUEST['updateSubmit'])) {
		if (!$GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
			$page->addError("Invalid Request");
		}
		elseif (!empty($_REQUEST['name']) && !$message->validName($_REQUEST['name'])) {
			$page->addError("Invalid name");
		}
		elseif (!empty($_REQUEST['target']) && !$message->validTarget($_REQUEST['target'])) {
			$page->addError("Invalid target");
		}
		elseif (!empty($_REQUEST['content']) && !$message->validContent($_REQUEST['content'])) {
			$page->addError("Invalid content");
		}
		else {
			if ($message->id) {
				app_log("Updating content_message '" . $message->target . "'", 'info');
				if (!$message->update(array('name' => $_REQUEST['name'], 'content' => $_REQUEST['content']))) {
					$page->addError("Cannot update block: " . $message->error());
				}
				else {
					$page->success = 'Updated Content Message';
				}
			}
			else {
				app_log("Adding content_message '" . $_REQUEST['target'] . "'", 'info');
				if (!$message->add(array('target' => $_REQUEST['target'], 'name' => $_REQUEST['name'], 'content' => $_REQUEST['content']))) {
					$page->addError("Cannot add block: " . $message->error());
				}
				else {
					$page->success = 'Added Content Message';
				}
			}
			if (isset($_REQUEST['addPage']) && $_REQUEST['addPage'] && !$page->errorCount()) {
				$addPage = new \Site\Page();
				if (!$page->add('content', 'index', $message->target)) {
					$page->addError("Cannot create page: " . $addPage->errorString());
				}
				else {
					$page->success = 'Added New Page';
				}
			}
			if ($message->id) {
				$parent_page = new \Site\Page();
				if (!$parent_page->getPage('content', 'index', $message->target)) $show_add_page = true;
			}
		}
	}

	$page->title = "Edit Site Content Block";

	// add tag to Content Message (using BaseModel unified tag system)
	if (!empty($_REQUEST['newSearchTag']) && empty($_REQUEST['removeSearchTag'])) {
		if (!empty($_REQUEST['newSearchTag']) && !empty($_REQUEST['newSearchTagCategory']) && 
			$message->validTagValue($_REQUEST['newSearchTag']) && 
			$message->validTagCategory($_REQUEST['newSearchTagCategory'])) {
			
			if ($message->addTag($_REQUEST['newSearchTag'], $_REQUEST['newSearchTagCategory'])) {
				$page->appendSuccess("Content Message Search Tag added Successfully");
			} else {
				$page->addError("Error adding Content Message search tag: " . $message->error());
			}
		} else {
			$page->addError("Value for Content Message Tag and Category are required");
		}
	}

	// remove tag from Content Message (using BaseModel unified tag system)
	if (!empty($_REQUEST['removeSearchTagId'])) {
		$searchTagXrefItem = new \Site\SearchTagXref($_REQUEST['removeSearchTagId']);
		if ($searchTagXrefItem->id) {
			$searchTag = new \Site\SearchTag($searchTagXrefItem->tag_id);
			if ($searchTag->id && $searchTag->class === 'Content::Message') {
				if ($message->removeTag($searchTag->value, $searchTag->category)) {
					$page->appendSuccess("Content Message Search Tag removed Successfully");
				} else {
					$page->addError("Error removing Content Message search tag: " . $message->error());
				}
			}
		}
	}

	// get tags for Content Message (using BaseModel unified tag system)
	$searchTagXref = new \Site\SearchTagXrefList();
	$searchTagXrefs = $searchTagXref->find(array("object_id" => $message->id, "class" => "Content::Message"));

	$registerCustomerSearchTags = array();
	foreach ($searchTagXrefs as $searchTagXrefItem) {
		$searchTag = new \Site\SearchTag();
		$searchTag->load($searchTagXrefItem->tag_id);
		$registerCustomerSearchTags[] = (object) array('searchTag' => $searchTag, 'xrefId' => $searchTagXrefItem->id);
	}

	// get unique categories and tags for autocomplete
	$searchTagList = new \Site\SearchTagList();
	$uniqueTagsData = $searchTagList->getUniqueCategoriesAndTagsJson();
