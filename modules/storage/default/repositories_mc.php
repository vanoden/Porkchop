<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage storage repositories');

    $repositoryList = new \Storage\RepositoryList();
    $repositories = $repositoryList->find();

	$page->title('Repositories');
	$page->setAdminMenuSection("Storage");  // Keep Storage section open
	$page->addBreadCrumb('Storage');