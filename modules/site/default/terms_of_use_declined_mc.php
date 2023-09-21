<?php
	$site = new \Site();
	$page = $site->page();

	$page->addError("Sorry, we are not allowed to provide this content without agreeing to the Terms of Use.");

	$target_page = new \Site\Page();
	$target_page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index']);