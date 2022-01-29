<?php
	$page = new \Site\Page();
	$page->requirePrivilege('content message browsing');

	$messageList = new \Content\MessageList();
	$messages = $messageList->find();
?>