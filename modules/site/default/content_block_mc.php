<?php
	$page = new \Site\Page();
	$page->requirePrivilege('edit content messages');

	if (isset($_REQUEST['id'])) {
		$message = new \Content\Message($_REQUEST['id']);
	}
	elseif ($_REQUEST['method'] == 'new') {
		// New Page, Load Nothing
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$message = new \Content\Message();
		if (! $message->get($GLOBALS['_REQUEST_']->query_vars_array[0])) {
			$message->name = $GLOBALS['_REQUEST_']->query_vars_array[0];
			$message->target = $GLOBALS['_REQUEST_']->query_vars_array[0];
		}
	}
	else {
		// Home Page
		$message = new \Content\Message();
		$message->get('');
	}
	//else {
	//	$message = new \Content\Message();
	//	if (! $message->get($GLOBALS['_REQUEST_']->index)) {
	//		$message->name = $GLOBALS['_REQUEST_']->index;
	//		$message->target = $GLOBALS['_REQUEST_']->index;
	//	}
	//}

	$show_add_page = false;

	if (isset($_REQUEST['Submit'])) {
		if ($message->id) {
			app_log("Updating content_message '".$message->target."'",'info');
			if (! $message->update(array('name' => $_REQUEST['name'], 'content' => $_REQUEST['content']))) {
				$page->addError("Cannot update block: ".$message->error());
			}
		}
		else {
			app_log("Adding content_message '".$_REQUEST['target']."'",'info');
			if (! $message->add(array('target' => $_REQUEST['target'],'name' => $_REQUEST['name'], 'content' => $_REQUEST['content']))) {
				$page->addError("Cannot add block: ".$message->error());
			}
		}
		if ($_REQUEST['addPage'] && ! $page->errorCount()) {
			$addPage = new \Site\Page();
			if (! $page->add('content','index',$message->target)) {
				$page->addError("Cannot create page: ".$addPage->errorString());
			}
		}
		if ($message->id) {
			$parent_page = new \Site\Page();
			if (! $parent_page->get('content','index',$message->target)) {
				$show_add_page = true;
			}
		}
	}
