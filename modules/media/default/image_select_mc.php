<?php
	if (!empty($_REQUEST['repository_id'])) {
		$repositoryBase = new \Storage\Repository($_REQUEST['repository_id']);
		$repository = $repositoryBase->getInstance();
	}
	else if (!empty($_REQUEST['repository_code'])) {
		$repositoryBase = new \Storage\Repository();
		$repository = $repositoryBase->getByCode($_REQUEST['repository_code']);
	}

	# Get Images to Display
	$imageList = new \Media\ImageList();
	$images = $imageList->find(array(
		'repository_id' => !empty($repository) ? $repository->id : null,
		'limit' => 100,
		'order_by' => 'edited',
		'order_direction' => 'DESC'
	));