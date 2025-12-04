<?php
	if (!empty($_REQUEST['repository_id'])) {
		$repositoryBase = new \Storage\Repository($_REQUEST['repository_id']);
		$repository = $repositoryBase->getInstance();
	}
	else if (!empty($_REQUEST['repository_code'])) {
		$repositoryBase = new \Storage\Repository();
		$repository = $repositoryBase->getByCode($_REQUEST['repository_code']);
	}

	if (!empty($_REQUEST['path'])) {
		$path = $_REQUEST['path'];
	}
	else {
		$path = '/';
	}

	# Get Images to Display
	$imageList = new \Media\ImageList();
	$images = $imageList->find(array(
		'repository_id' => !empty($repository) ? $repository->id : null,
		'path' => $path,
		'limit' => 100,
		'order_by' => 'edited',
		'order_direction' => 'DESC'
	));
	
	# Check for errors
	if ($imageList->error()) {
		app_log("Error finding images: " . $imageList->error(), 'error');
		$images = array();
	}
	
	# If no images found, show message
	if (empty($images)) {
		app_log("No images found for repository", 'notice');
	}
