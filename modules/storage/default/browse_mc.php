<?php
    $page = new \Site\Page();
    $page->requirePrivilege('manage storage repositories');

	if (! isset($_REQUEST['path']) || strlen($_REQUEST['path']) < 1) $_REQUEST['path'] = '/';

    $repository = new \Storage\Repository();
    $repository->get($_REQUEST['code']);
    if ($repository->error()) {
        $page->addError($repository->error());
    } elseif(! $repository->id) {
        $page->addError("Repository not found");
    } else {
		$directories = $repository->directories($_REQUEST['path']);
		$files = $repository->files($_REQUEST['path']);
    }

	$page->title = $repository->name;
	$page->addBreadcrumb("Storage");
	$page->addBreadcrumb("Repositories",'/_storage/repositories');
	if ($repository->id) {
		$page->addBreadcrumb($repository->name,'/_storage/repository/'.$repository->code);
		$page->addBreadcrumb("Browse");
	}