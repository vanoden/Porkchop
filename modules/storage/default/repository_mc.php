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
	else {
		// Default to Local Repository
		$repository = $factory->create('local');
	
	}

	// Handle Form Submission
	if (isset($_REQUEST['btn_submit']) && ! $page->errorCount()) {
		// Confirm CSRF Token
		if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
			$page->addError("Invalid Token");
		}
		else {
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
			
			// Fetch Keys for this Repository Type
			$metadata_keys = $repository->metadata_keys();
			foreach ($metadata_keys as $key) {
				if (!$repository->validMetadata($key,$_REQUEST[$key])) {
					$page->addError("Invalid value for $key");
					$_REQUEST[$key] = htmlspecialchars($_REQUEST[$key]);
				}
			}

			// No Errors, Process Form
			if ($page->errorCount() < 1) {
				$parameters = array();
				$parameters['name'] = $_REQUEST['name'];
				if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
				$parameters['status'] = $_REQUEST['status'];

				$metadata_keys = $repository->metadta_keys();
				foreach ($metadata_keys as $key) {
					$parameters[$key] = $_REQUEST[$key];
				}

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
				$privilege_json = $privilegeList->toJSON();
				if (!$repository->update(array('default_privileges_json' => $privilege_json))) {
					$page->addError("Error updating privileges: ".$repository->error()."\n");
				}

				// Set record values
				if ($repository->error()) {
					$page->addError($repository->error());
					$page->success = null;

					// Keep form fields populated
					$form['code'] = $_REQUEST['code'];
					$form['name'] = $_REQUEST['name'];
					$form['type'] = $_REQUEST['type'];
					$form['status'] = $_REQUEST['status'];
					foreach ($metadata_keys as $key) {
						$form[$key] = $_REQUEST[$key];
					}
				}
				else {
					// Load New Repository
					//$repository = $factory->get($repository->code);
					//$repository->_setMetadata('path',$_REQUEST['path']);
					//$repository->_setMetadata('endpoint',$_REQUEST['endpoint']);
					//if (isset($_REQUEST['type']) && $_REQUEST['type'] == 's3') {
					//	$repository->_setMetadata('accessKey', $_REQUEST['accessKey']);
					//	$repository->_setMetadata('secretKey', $_REQUEST['secretKey']);
					//	$repository->_setMetadata('bucket', $_REQUEST['bucket']);
					//	$repository->_setMetadata('region', $_REQUEST['region']);
					//}

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
					foreach ($metadata_keys as $key) {
						$form[$key] = $repository->_metadata($key);
					}
				}
			}
			else {
				$form['code'] = $_REQUEST['code'];
				$form['name'] = $_REQUEST['name'];
				$form['type'] = $_REQUEST['type'];
				$form['status'] = $_REQUEST['status'];
				$metadata_keys = $repository->metadata_keys();
				foreach ($metadata_keys as $key) {
					$form[$key] = $_REQUEST[$key];
				}
			}
		}
	}
	// No Submit, Show Repository Details
	elseif (! $page->errorCount()) {
		if ($repository->id) {
			$form['code'] = $repository->code;
			$form['name'] = $repository->name;
			$form['type'] = $repository->type;
			$form['status'] = $repository->status;
			$metadata_keys = $repository->metadata_keys();
			foreach ($metadata_keys as $key) {
				$form[$key] = $repository->_metadata($key);
			}
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
		$metadata_keys = $repository->metadata_keys();
		foreach ($metadata_keys as $key) {
			$form[$key] = $_REQUEST[$key];
		}
	}

	$default_privileges = $repository->default_privileges();

	$page->title("Storage Repository");
	if ($repository->id) $page->instructions = "Update values and click Submit to update repository setting";
	else $page->instructions = "Fill out form and click Submit to create a new Storage Repository";
	$page->addBreadCrumb("Repositories","/_storage/repositories");
	if ($repository->id) $page->addBreadCrumb($repository->name,"/_storage/repository?id=".$repository->id);
	else $page->addBreadCrumb("New Repository");