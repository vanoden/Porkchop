<?php
	// Initialize Page
	$site = new \Site();
	$page = $site->page();

	// Authorization
	$page->requirePrivilege('manage storage repositories');

	$factory = new \Storage\RepositoryFactory();
	$repository = new \Storage\Repository();

	// Identify File from User Input
	if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
		// POST/GET Variable with Repository ID
		$repository = $factory->load($_REQUEST['id']);
		if ($factory->error()) $page->addError("Cannot load repository #".$_REQUEST['id'].": ".$factory->error());
	}
	elseif (!empty($_REQUEST['code'])) {
		// POST/GET Variable with Repository Code
		$repository = $factory->get($_REQUEST['code']);
	}
	elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		// Query String with Repository Code
		$repository->get($GLOBALS['_REQUEST_']->query_vars_array[0]);
	}
	elseif ($repository->validType($_REQUEST['type'])) {
		// POST/GET Variable with Repository Type
		$repository = $factory->create($_REQUEST['type']);
	}

	// Handle Form Submission
	if (isset($_REQUEST['btn_submit']) && ! $page->errorCount()) {
		// Confirm CSRF Token
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
			$page->addError("Invalid Token");
		}

		// Validate Form Fields
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
		if (isset($_REQUEST['type']) && preg_match('/^s3$/i',$_REQUEST['type'])) {
			if (!$repository->validAccessKey($_REQUEST['accessKey'])) {
				$page->addError("Invalid access key '".$_REQUEST['accessKey']."'");
				$_REQUEST['accessKey'] = htmlspecialchars($_REQUEST['accessKey']);
			}
			if (!$repository->validSecretKey($_REQUEST['secretKey'])) {
				$page->addError("Invalid secretKey '".$_REQUEST['secretKey']."'");
				$_REQUEST['secretKey'] = htmlspecialchars($_REQUEST['secretKey']);
			}
			if (!$repository->validBucket($_REQUEST['bucket'])) {
				$page->addError("Invalid bucket '".$_REQUEST['bucket']."'");
				$_REQUEST['bucket'] = htmlspecialchars($_REQUEST['bucket']);
			}
			if (!$repository->validRegion($_REQUEST['region'])) {
				$page->addError("Invalid region '".$_REQUEST['region']."'");
				$_REQUEST['region'] = htmlspecialchars($_REQUEST['region']);
			}
		}
		else if (!$repository->validEndpoint($_REQUEST['endpoint'])) {
			$page->addError("Invalid endpoint");
			$_REQUEST['endpoint'] = htmlspecialchars($_REQUEST['endpoint']);
		}

		// No Errors, Process Form
		if ($page->errorCount() < 1) {
			$parameters = array();
			$parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			$parameters['status'] = $_REQUEST['status'];
			$parameters['path'] = $_REQUEST['path'];
			$parameters['endpoint'] = $_REQUEST['endpoint'];
			$parameters['accessKey'] = $_REQUEST['accessKey'];
			$parameters['secretKey'] = $_REQUEST['secretKey'];
			$parameters['bucket'] = $_REQUEST['bucket'];
			$parameters['region'] = $_REQUEST['region'];

			// Update record if id is set
			if ($repository->id) {
				$repository->update($parameters);
				$page->success = "Repository updated";
			}
			// Create new record
			else {
				$repository = $factory->create($_REQUEST['type']);
				if ($factory->error()) $page->addError($factory->error());
				$repository->add($parameters);           
				$page->success = "Repository created";
			}

			/********************************************/
			/* Update Privileges						*/
			/********************************************/
			$privilegeList = new \Resource\PrivilegeList();
			// Parse Existing Privilege Data
			$privilegeList->fromJSON($repository->default_privileges_json);
			// Apply Form Edits to Existing Privileges
			$privilegeList->apply($_REQUEST['privilege']);
			if ($privilegeList->error()) $page->error($privilegeList->error());
			if (!empty($privilegeList->message)) $page->appendSuccess($privilegeList->message);
			// Add New Privileges
			$privilegeList->grant($_REQUEST['new_privilege_entity_type'],$_REQUEST['new_privilege_entity_id'],$_REQUEST['new_privilege_read'],$_REQUEST['new_privilege_write']);
			// Update Repository Record with Updated Privileges
			$repository->update(array('default_privileges_json' => $privilegeList->toJSON()));

			// Set record values
			if ($repository->error()) {
				$page->addError($repository->error());
				$page->success = null;

				// Keep form fields populated
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
				// Load New Repository
				$repository = $factory->get($repository->code);
				$repository->_setMetadata('path',$_REQUEST['path']);
				$repository->_setMetadata('endpoint',$_REQUEST['endpoint']);
				if (isset($_REQUEST['type']) && $_REQUEST['type'] == 's3') {
					$repository->_setMetadata('accessKey', $_REQUEST['accessKey']);
					$repository->_setMetadata('secretKey', $_REQUEST['secretKey']);
					$repository->_setMetadata('bucket', $_REQUEST['bucket']);
					$repository->_setMetadata('region', $_REQUEST['region']);
				}

				// Test Connection
				if ($repository->connect()) {
					$page->appendSuccess("Connection tested");
				}
				else {
					$page->addError("Connection failed: ".$repository->error());
				}

				// Populate Form Fields
				$form['code'] = $repository->code;
				$form['name'] = $repository->name;
				$form['type'] = $repository->type;
				$form['status'] = $repository->status;
				$form['path'] = $repository->_metadata('path');
				$form['endpoint'] = $repository->_metadata('endpoint');
				if ($repository->type == 's3') {
					$form['accessKey'] = $repository->_metadata('accessKey');
					$form['secretKey'] = $repository->_metadata('secretKey');
					$form['bucket'] = $repository->_metadata('bucket');
					$form['region'] = $repository->_metadata('region');
				}
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
	// No Submit, Show Repository Details
	elseif (! $page->errorCount()) {
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
	// Repopulate Form with Request Data
	elseif (!empty($_REQUEST['name'])) {
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

	$default_privileges = $repository->default_privileges();

	$page->title("Storage Repository");
	if ($repository->id) $page->instructions = "Update values and click Submit to update repository setting";
	else $page->instructions = "Fill out form and click Submit to create a new Storage Repository";
	$page->addBreadCrumb("Repositories","/_storage/repositories");
	if ($repository->id) $page->addBreadCrumb($repository->name,"/_storage/repository?id=".$repository->id);
	else $page->addBreadCrumb("New Repository");