<?php
	$page = new \Site\Page();
	$page->requireRole('storage manager');

	$factory = new \Storage\RepositoryFactory();

	if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
		$repository = $factory->load($_REQUEST['id']);
		if ($factory->error) $page->addError("Cannot load repository #".$_REQUEST['id'].": ".$factory->error);
	}

	if (isset($_REQUEST['btn_submit']) && ! $page->errorCount()) {
		$parameters = array();
		$parameters['name'] = $_REQUEST['name'];
		$parameters['type'] = $_REQUEST['type'];
		$parameters['status'] = $_REQUEST['status'];
        $parameters['path'] = $_REQUEST['path'];
        $parameters['endpoint'] = $_REQUEST['endpoint'];
        $parameters['accessKey'] = $_REQUEST['accessKey'];
        $parameters['secretKey'] = $_REQUEST['secretKey'];
        $parameters['bucket'] = $_REQUEST['bucket'];
        $parameters['region'] = $_REQUEST['region'];
            
        if ($repository->id) {
            $repository->update($parameters);
            $page->success = "Repository updated";
        } else {
			$repository = $factory->create($_REQUEST['type']);
			if ($factory->error) $page->addError($factory->error);
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
    	    $form['accessKey'] = $_REQUEST['accessKey'];
    	    $form['secretKey'] = $_REQUEST['secretKey'];
    	    $form['region'] = $_REQUEST['region'];
    	    $form['bucket'] = $_REQUEST['bucket'];
		} else {
		    $repository = $factory->get($repository->code);
    		$repository->_setMetadata('path',$_REQUEST['path']);
        	$repository->_setMetadata('endpoint',$_REQUEST['endpoint']);
            if ($_REQUEST['type'] == 's3') {
	            $repository->_setMetadata('accessKey', $_REQUEST['accessKey']);
		        $repository->_setMetadata('secretKey', $_REQUEST['secretKey']);
		        $repository->_setMetadata('bucket', $_REQUEST['bucket']);
		        $repository->_setMetadata('region', $_REQUEST['region']);            
            } 
            $form['code'] = $repository->code;
            $form['name'] = $repository->name;
            $form['type'] = $repository->type;
            $form['status'] = $repository->status;
    	    $form['path'] = $repository->_metadata('path');
    	    $form['endpoint'] = $repository->_metadata('endpoint');
            if ($_REQUEST['type'] == 's3') {
        	    if (isset($repository->accessKey)) $form['accessKey'] = $repository->accessKey;
        	    if (isset($repository->secretKey)) $form['secretKey'] = $repository->secretKey;
        	    if (isset($repository->region)) $form['region'] = $repository->region;
        	    if (isset($repository->bucket)) $form['bucket'] = $repository->bucket;
            }
        }
	} elseif (! $page->errorCount()) {
	
		if (isset($_REQUEST['code'])) {
			$repository = $factory->get($_REQUEST['code']);
			if ($factory->error) $page->addError("Cannot load repository '".$_REQUEST['code']."': ".$factory->error);
		}
		
		if ($repository->id) {
	        $form['code'] = $repository->code;
    	    $form['name'] = $repository->name;
    	    $form['type'] = $repository->type;
    	    $form['status'] = $repository->status;
    	    $form['path'] = $repository->_metadata('path');
    	    $form['endpoint'] = $repository->_metadata('endpoint');
    	    $form['accessKey'] = $repository->_metadata('accessKey');
    	    $form['secretKey'] = $repository->_metadata('secretKey');
    	    $form['region'] = $repository->_metadata('region');
    	    $form['bucket'] = $repository->_metadata('bucket');
		}
    }
