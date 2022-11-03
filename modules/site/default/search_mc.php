<?php
	$page = new \Site\Page();
    if (isset($_REQUEST['string']) && !empty($_REQUEST['string'])) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_POST['csrfToken'])) {
        	$page->addError("Invalid Request");
        } else {
            // message list
            $messageList = new \Content\MessageList();
            $messages = $messageList->search(array('string'=>$_REQUEST['string'], 'is_user_search' => true));
        }
	}
