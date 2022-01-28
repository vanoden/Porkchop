<?php
	if (isset($_REQUEST['id'])) {
		$message = new \Content\Message($_REQUEST['id']);
	}
	else {
		$message = new \Content\Message($GLOBALS['_REQUEST_']->index);
		if (! $message->id) {
			$message->name = $GLOBALS['_REQUEST_']->index;
			$message->target = $GLOBALS['_REQUEST_']->index;
		}
	}

	if (isset($_REQUEST['Submit'])) {
		if ($message->id) {
			app_log("Updating content_message '".$message->target."'",'info');
			$message->update(array('name' => $_REQUEST['name'], 'content' => $_REQUEST['content']));
		}
		else {
			app_log("Adding content_message '".$_REQUEST['target']."'",'info');
			$message->add(array('target' => $_REQUEST['target'],'name' => $_REQUEST['name'], 'content' => $_REQUEST['content']));
		}
	}