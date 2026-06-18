<?php
	/** @view /_media/image_select
	 * @description Modal for selecting an image from a repository, used in media fields when adding/editing objects
	 */
	$porkchop = new Porkchop();
	$site = $porkchop->site();
	$page = $site->page();

	$repository = null;
	$repositoryFactory = new \Storage\RepositoryFactory();
	if (!empty($_REQUEST['repository_id'])) {
		$repository = $repositoryFactory->createWithID($_REQUEST['repository_id']);
	}
	elseif (!empty($_REQUEST['repository_code'])) {
		$repository = $repositoryFactory->createWithCode($_REQUEST['repository_code']);
	}

	$path = !empty($_REQUEST['path']) ? (string)$_REQUEST['path'] : '/';
	if (!preg_match('/^\//', $path)) {
		$path = '/' . $path;
	}

	// Legacy path aliases (uploads use spectros_* paths)
	$pathAliases = array(
		'/product_image' => '/spectros_product_image',
	);
	if (isset($pathAliases[$path])) {
		$path = $pathAliases[$path];
	}

	$images = array();
	$listError = '';

	if (empty($repository) || empty($repository->id)) {
		$listError = 'Repository not found.';
		app_log("image_select: repository not found", 'notice');
	}
	else {
		$imageList = new \Media\ImageList();
		$images = $imageList->find(array(
			'repository_id' => $repository->id,
			'path' => $path,
			'limit' => 100,
			'order_by' => 'edited',
			'order_direction' => 'DESC'
		));

		if ($imageList->error()) {
			$listError = $imageList->error();
			app_log("Error finding images: " . $listError, 'error');
			$images = array();
		}
		elseif (empty($images)) {
			app_log("No images found for repository {$repository->code} at path {$path}", 'notice');
		}
	}
