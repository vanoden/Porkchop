<?php
    $site = new \Site();
	$page = $site->page();
	$page->requirePrivilege('manage storage repositories');

	$factory = new \Storage\RepositoryFactory();

	if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
		$repository = $factory->load($_REQUEST['id']);
		if ($factory->error()) $page->addError("Cannot load repository #".$_REQUEST['id'].": ".$factory->error());
	}
    else {
        $repository = new \Storage\Repository();
    }

    if (isset($_REQUEST['btn_submit']) && ! $page->errorCount()) {
        if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
            $page->addError("Invalid Token");
        }
		if (!$repository->validName($_REQUEST['name'])) {
			$page->addError("Invalid name");
			$_REQUEST['name'] = htmlspecialchars($_REQUEST['name']);
		}
		if (empty($repository->id) && !$repository->validType($_REQUEST['type'])) {
			$page->addError("Invalid type '".$_REQUEST['type']."'");
			$_REQUEST['type'] = htmlspecialchars($_REQUEST['type']);
		}
		if (!$repository->validStatus($_REQUEST['status'])) {
			$page->addError("Invalid status");
			$_REQUEST['status'] = htmlspecialchars($_REQUEST['status']);
		}
		if (!$repository->validPath($_REQUEST['path'])) {
			$page->addError("Invalid path");
			$_REQUEST['path'] = htmlspecialchars($_REQUEST['path']);
		}
		if (!$repository->validEndpoint($_REQUEST['endpoint'])) {
			$page->addError("Invalid endpoint");
			$_REQUEST['endpoint'] = htmlspecialchars($_REQUEST['endpoint']);
		}
		if (!$repository->validAccessKey($_REQUEST['endpoint'])) {
			$page->addError("Invalid endpoint");
			$_REQUEST['endpoint'] = htmlspecialchars($_REQUEST['endpoint']);
		}
		if (!$repository->validSecretKey($_REQUEST['secretKey'])) {
			$page->addError("Invalid secretKey");
			$_REQUEST['secretKey'] = htmlspecialchars($_REQUEST['secretKey']);
		}
		if (!$repository->validBucket($_REQUEST['bucket'])) {
			$page->addError("Invalid bucket");
			$_REQUEST['bucket'] = htmlspecialchars($_REQUEST['bucket']);
		}
		if (!$repository->validRegion($_REQUEST['region'])) {
			$page->addError("Invalid region");
			$_REQUEST['region'] = htmlspecialchars($_REQUEST['region']);
		}

		if ($page->errorCount() < 1) {
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
			}
            else {
				$repository = $factory->create($_REQUEST['type']);
				if ($factory->error()) $page->addError($factory->error());
				$repository->add($parameters);           
				$page->success = "Repository created";
			}

			if ($repository->error()) {
				$page->addError($repository->error());
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
			}
            else {
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

			// Update Privileges
			if (!empty($_REQUEST['d_privilege_type'][0])) {
				
			}
		}
		else {
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
		}
	}
    elseif (! $page->errorCount()) {
	
		if (isset($_REQUEST['code'])) {
			$repository = $factory->get($_REQUEST['code']);
			if ($factory->error()) $page->addError("Cannot load repository '".$_REQUEST['code']."': ".$factory->error());
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
			$default_privileges = $repository->default_privileges();
			$override_privileges = $repository->override_privileges();
		}
    }

    $page->title("Storage Repository");
	if ($repository->id) $page->instructions = "Update values and click Submit to update repository setting";
	else $page->instructions = "Fill out form and click Submit to create a new Storage Repository";
	$page->addBreadCrumb("Repositories","/_storage/repositories");
    if ($repository->id) $page->addBreadCrumb($repository->name,"/_storage/repository?id=".$repository->id);
	else $page->addBreadCrumb("New Repository");