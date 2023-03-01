<?php
	$page = new \Site\Page();

	$path = $GLOBALS['_REQUEST_']->query_vars;

	if (preg_match('/^(\w[^\/]*)\/(.*)/',$path,$matches)) {
		$repository_string = $matches[1];
		$path_string = $matches[2];
		$repositoryList = new \Storage\RepositoryList();
		$repository = $repositoryList->find(array('name' => $repository_string))[0];
		if (!empty($repository)) {
			$file = $repository->getFileFromPath($path_string);
			if (empty($file)) {
				$page->addError("File '$path_string' not found");
				app_log("File '$path_string' requested but not found",'warn');
				return 404;
			}
			else {
				header('Content-Type: '.$file->mime_type);
				header('Size: '.$file->size);
				$file->download();
				exit();
			}
		}
		else {
			$page->addError("Repository not found");
			app_log("Repository '$repository_string' requested but not found",'warn');
			return 404;
		}
	}
	else {
		$page->addError("Error: $path invalid");
		app_log("Invalid path '$path' requested",'warn');
	}