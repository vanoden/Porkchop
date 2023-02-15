<?php
	$site = new \Site();
	$page = $site->page();;
	$page->requirePrivilege('content message browsing');

	$messageList = new \Content\MessageList();
	$messages = $messageList->find();
	if ($messageList->error()) $page->addError($messageList->error());
?>