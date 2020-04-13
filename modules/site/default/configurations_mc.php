<?php
	$page = new \Site\Page();
	$page->fromRequest();
	$page->requireRole('support user');
	
	$siteConfigurations = new \Site\ConfigurationList();
	$configuration = $siteConfigurations->find();
