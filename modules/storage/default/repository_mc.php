<?php
	$page = new \Site\Page();
	$page->requireRole('storage manager');

	if (isset($_REQUEST['code'])) {
		$factory = new \Storage\RepositoryFactory();
		$repository = $factory->get($_REQUEST['code']);
		if ($factory->error) {
			$page->addError($factory->error);
		}
	}
	elseif (isset($_REQUEST['id'])) {
		$factory = new \Storage\RepositoryFactory();
		$repository = $factory->load($_REQUEST['id']);
		if ($factory->error) {
			$page->addError($factory->error);
		}
	}

	if ($_REQUEST['btn_submit']) {
		$parameters = array();
		$parameters['name'] = $_REQUEST['name'];
		$parameters['type'] = $_REQUEST['type'];
		$parameters['status'] = $_REQUEST['status'];
        $parameters['path'] = $_REQUEST['path'];
        $parameters['endpoint'] = $_REQUEST['endpoint'];
        if ($repository->id) {
            $repository->update($parameters);
            $page->success = "Repository updated";
        }
		else {
            $repository->add($parameters);
            $page->success = "Repository created";
        }

		if ($repository->error) {
            $page->addError($repository->error);
            $page->success = null;
            $form['code'] = $_REQUEST['code'];
            $form['name'] = $_REQUEST['name'];
            $form['type'] = $_REQUEST['type'];
            $form['status'] = $_REQUEST['status'];
            $form['path'] = $_REQUEST['path'];
            $form['endpoint'] = $_REQUEST['endpoint'];
		}
        else {
    		$repository->setMetadata('path',$_REQUEST['path']);
        	$repository->setMetadata('endpoint',$_REQUEST['endpoint']);
            $form['code'] = $repository->code;
            $form['name'] = $repository->name;
            $form['type'] = $repository->type;
            $form['status'] = $repository->status;
            $form['path'] = $repository->path;
            $form['endpoint'] = $repository->endpoint;
        }
	}
    else {
        $form['code'] = $repository->code;
        $form['name'] = $repository->name;
        $form['type'] = $repository->type;
        $form['status'] = $repository->status;
        $form['path'] = $repository->path;
        $form['endpoint'] = $repository->endpoint;
    }
?>