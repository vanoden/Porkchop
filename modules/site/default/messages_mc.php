<?php
	$page = new \Site\Page();
	//$page->requireAuth();

	if (empty($GLOBALS['_SESSION_']->customer->organization->id)) $page->addError("Your registration has not been completed.  Please make sure you've validated your email and contact ".$GLOBALS['_config']->site->support_email.' for assistance.');
	$siteMessages = new \Site\SiteMessagesList();
	//$userMessages = $siteMessages->find(array('user_created'=>$GLOBALS['_SESSION_']->customer->id));
	
	$userMessages = $siteMessages->find(array('user_created'=>2612));
