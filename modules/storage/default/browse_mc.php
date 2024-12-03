<?php
	$site = new \Site();
    $page = $site->page();
    $page->requirePrivilege('manage storage repositories');

	/****************************************/
	/* Validate Form Data					*/
	/****************************************/
	// Default File Path if unset
	if (! isset($_REQUEST['path']) || strlen($_REQUEST['path']) < 1) $_REQUEST['path'] = '/';

	// Load Requested Repository
    $repoFactory = new \Storage\RepositoryFactory();
    $repository = $repoFactory->get($_REQUEST['code']);
    if ($repoFactory->error()) {
        $page->addError($repoFactory->error());
    }
	elseif(! $repository->id) {
        $page->addError("Repository not found");
    }
	else {
		$directories = $repository->directories($_REQUEST['path']);
		$files = $repository->files($_REQUEST['path']);
    }

	/****************************************/
	/* Handle Form Actions					*/
	/****************************************/
	if ($_REQUEST['method'] == 'deleteFile') {
		$file = new \Storage\File($_REQUEST['file_id']);
		if (! $file->exists()) {
			$page->addError("File not found");
		}
		else {
			if ($repository->deleteFile($file->id)) $page->appendSuccess("File deleted");
			else $page->addError($repository->error());
		}
	}

	/****************************************/
	/* Page Title and Breadcrumbs			*/
	/****************************************/
	$page->title = $repository->name;
	$page->addBreadcrumb("Storage");
	$page->addBreadcrumb("Repositories",'/_storage/repositories');
	if ($repository->id) {
		$page->addBreadcrumb($repository->name,'/_storage/repository/'.$repository->code);
		$page->addBreadcrumb("Browse");
	}
