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
		$message = new \Content\Message();
		if (! $message->get($GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$message->name = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$message->target = $GLOBALS['_REQUEST_']->query_vars_array[0];
		}
	}
	elseif(!empty($GLOBALS['_REQUEST_']->index)) {
		$message = new \Content\Message();
		if (! $message->get($GLOBALS['_REQUEST_']->index)) {
			$message->name = $GLOBALS['_REQUEST_']->index;
			$message->target = $GLOBALS['_REQUEST_']->index;
		}
	}
	else {
		// Home Page
		$message = new \Content\Message();
		$message->get('');
	}
	$show_add_page = false;

    // handle page submit
	if (isset($_REQUEST['Submit'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
            $page->addError("Invalid Request");
	    }
		elseif (!empty($_REQUEST['name']) && ! $message->validName($_REQUEST['name'])) {
			$page->addError("Invalid name");
		}
		elseif (!empty($_REQUEST['target']) && ! $message->validTarget($_REQUEST['target'])) {
			$page->addError("Invalid target");
		}
		elseif (!empty($_REQUEST['content']) && ! $message->validContent($_REQUEST['content'])) {
			$page->addError("Invalid content");
		}
		else {
		    if ($message->id) {
			    app_log("Updating content_message '".$message->target."'",'info');
			    if (! $message->update(array('name' => $_REQUEST['name'], 'content' => $_REQUEST['content']))) {
				    $page->addError("Cannot update block: ".$message->error());
			    } else {
    			    $page->success = 'Updated Content Message';
			    }
		    }
			else {
			    app_log("Adding content_message '".$_REQUEST['target']."'",'info');
			    if (! $message->add(array('target' => $_REQUEST['target'],'name' => $_REQUEST['name'], 'content' => $_REQUEST['content']))) {
    			    $page->addError("Cannot add block: ".$message->error());
			    } else {
    			    $page->success = 'Added Content Message';
			    }
		    }
		    if ($_REQUEST['addPage'] && ! $page->errorCount()) {
			    $addPage = new \Site\Page();
			    if (! $page->add('content','index',$message->target)) {
                    $page->addError("Cannot create page: ".$addPage->errorString());
			    } else {
    			    $page->success = 'Added New Page';
			    }
		    }
		    if ($message->id) {
			    $parent_page = new \Site\Page();
			    if (! $parent_page->getPage('content','index',$message->target)) $show_add_page = true;
		    }
	    }
	}
