<?php
$site = new \Site();
$page = $site->page();
$page->requirePrivilege('manage storage repositories');
$request = new \HTTP\Request();
$can_proceed = true;

/****************************************/
/* Validate Form Data					*/
/****************************************/
// Get the path parameter with validation
$path = $_REQUEST['path'] ?? '/';
if (!$request->validText($path) || strlen($path) < 1) {
	$path = '/';
}

// Load Requested Repository
$repo_code = $_REQUEST['code'] ?? null;
if (!$request->validText($repo_code)) {
	$page->addError("Invalid repository code");
	$can_proceed = false;
} else {
	$repoFactory = new \Storage\RepositoryFactory();
	$repository = $repoFactory->get($repo_code);
	if ($repoFactory->error()) {
		$page->addError($repoFactory->error());
		$can_proceed = false;
	} elseif (! $repository->id) {
		$page->addError("Repository not found");
		$can_proceed = false;
	} else {
		$directories = $repository->directories($path);
		$files = $repository->files($path);
	}
}

/****************************************/
/* Handle Form Actions					*/
/****************************************/
$method = $_REQUEST['method'] ?? null;
if ($request->validText($method) && $method == 'deleteFile' && $can_proceed) {
	$file_id = $_REQUEST['file_id'] ?? null;
	if (!$request->validInteger($file_id)) {
		$page->addError("Invalid file ID");
	} else {
		$file = new \Storage\File($file_id);
		if (! $file->exists()) {
			$page->addError("File not found");
		} else {
			if ($repository->deleteFileFromDb($file->id)) $page->appendSuccess("File deleted");
			else $page->addError($repository->error());
		}
	}
}

/****************************************/
/* Page Title and Breadcrumbs			*/
/****************************************/
if (isset($repository) && $repository->id) {
	$page->title = $repository->name;
	$page->addBreadcrumb("Storage");
	$page->addBreadcrumb("Repositories", '/_storage/repositories');
	$page->addBreadcrumb($repository->name, '/_storage/repository/' . $repository->code);
	$page->addBreadcrumb("Browse");
}
