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
			header('Content-Type: '.$file->mime_type);
			header('Size: '.$file->size);
			$file->download();
		}
	}
	else {
		print "Error: $path invalid<br>\n";
	}
	exit;