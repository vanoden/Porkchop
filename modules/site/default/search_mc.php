<?php
	$page = new \Site\Page();
	
	// message list
	$messageList = new \Content\MessageList();
	$messages = $messageList->search(array('string'=>$_REQUEST['string'], 'is_user_search' => true));
