<?php
// Initialize Page
$site = new \Site();
$page = $site->page();

// Authorization
$page->requirePrivilege('manage storage repositories');

// Identify File from User Input
$factory = new \Storage\RepositoryFactory();
if (isset($_REQUEST['id']) && $_REQUEST['id'] > 0) {
	// POST/GET Variable with Repository ID
	$repository = $factory->createWithID($_REQUEST['id']);
}
elseif (!empty($_REQUEST['code'])) {
	// POST/GET Variable with Repository Code
	$repository = $factory->createWithCode($_REQUEST['code']);
}
elseif (!empty($GLOBALS['_REQUEST_']->query_vars_array[0])) {
	// Query String with Repository Code
	$repository = $factory->createWithCode($GLOBALS['_REQUEST_']->query_vars_array[0]);
}
else {
	// Default to Virtual Repository
	$repository = $factory->create('virtual');
}

$repository_types = $factory->types();

// Handle Form Submission
if (isset($_REQUEST['btn_submit']) && ! $page->errorCount()) {
	// Confirm CSRF Token
	if (! $GLOBALS['_SESSION_']->verifyCSRFToken($_REQUEST['csrfToken'])) {
		$page->addError("Invalid Token");
	} else {
		// Validate Form Fields
		if (!$repository->validName($_REQUEST['name'])) {
			$page->addError("Invalid name");
			$_REQUEST['name'] = htmlspecialchars($_REQUEST['name']);
		}
		if (empty($repository->id) && isset($_REQUEST['type']) && !$repository->validType($_REQUEST['type'])) {
			$page->addError("Invalid type '" . $_REQUEST['type'] . "'");
			$_REQUEST['type'] = htmlspecialchars($_REQUEST['type']);
		}
		if (isset($_REQUEST['status']) && !$repository->validStatus($_REQUEST['status'])) {
			$page->addError("Invalid status");
			$_REQUEST['status'] = htmlspecialchars($_REQUEST['status']);
		}

		// For new repositories, create the proper repository type before validation
		if (empty($repository->id) && !empty($_REQUEST['type']) && isset($_REQUEST['type']) && $repository->validType($_REQUEST['type'])) {
			$repository = $factory->create($_REQUEST['type']);
			if ($factory->error()) $page->addError($factory->error());
			// If factory returns false (unsupported type), fall back to base repository
			if (!$repository) {
				$page->addError("Repository type '" . (isset($_REQUEST['type']) ? $_REQUEST['type'] : '') . "' is not supported");
				$repository = $factory->create('Validation');
			}
		}

		// Fetch Keys for this Repository Type
		$metadata_keys = $repository->getImpliedMetadataKeys();
		foreach ($metadata_keys as $key) {
			// Skip validation for empty accessKey and secretKey on S3 repositories (IAM role support)
			if ($repository->type == 's3' && ($key == 'accessKey' || $key == 'secretKey') && empty($_REQUEST[$key])) continue;
			if (!$repository->validMetadata($key, $_REQUEST[$key])) {
				if ($repository->error()) $page->addWarning($repository->error());
				else $page->addWarning("Invalid value '" . $_REQUEST[$key] . "' for $key");
				$_REQUEST[$key] = htmlspecialchars($_REQUEST[$key]);
			}
		}

		// No Errors, Process Form
		if ($page->errorCount() < 1) {
			$parameters = array();
			$parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			$parameters['status'] = $_REQUEST['status'];

			$metadata_keys = $repository->getImpliedMetadataKeys();
			foreach ($metadata_keys as $key) {
				$parameters[$key] = $_REQUEST[$key];
			}
			
			// Debug logging for S3 repositories
			if (isset($_REQUEST['type']) && $_REQUEST['type'] == 's3') {
				app_log("S3 Repository creation - bucket parameter: '" . (isset($_REQUEST['bucket']) ? $_REQUEST['bucket'] : '') . "'", 'debug');
				app_log("S3 Repository creation - all metadata: " . print_r($parameters, true), 'debug');
			}

			// Update record if id is set
			if ($repository->id) {
				$repository->update($parameters);
				$page->success = "Repository updated";
			}
			// Create new record
			else {
				// Only create repository object if not already created during validation
				if ($repository->id || get_class($repository) == 'Storage\Repository') {
					$factory = new \Storage\RepositoryFactory();
					$repository = $factory->create($_REQUEST['type']);
					if ($factory->error()) $page->addError($factory->error());
					// If factory returns false (unsupported type), fall back to base repository
					if (!$repository) {
						$page->addError("Repository type '" . $_REQUEST['type'] . "' is not supported");
						$repository = $factory->create('Validation');
					}
				}
				if (isset($_REQUEST['type']) && $_REQUEST['type'] == 'local' && isset($_REQUEST['path'])) $parameters['path'] = $_REQUEST['path'];
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
			if (isset($_REQUEST['privilege'])) {
				$privilegeList->apply($_REQUEST['privilege']);
			}
			if ($privilegeList->error()) $page->error($privilegeList->error());
			if (!empty($privilegeList->message)) $page->appendSuccess($privilegeList->message);
			// Add New Privileges
			if (isset($_REQUEST['new_privilege_entity_type']) && isset($_REQUEST['new_privilege_entity_id']) && isset($_REQUEST['new_privilege_read']) && isset($_REQUEST['new_privilege_write'])) {
				$privilegeList->grant($_REQUEST['new_privilege_entity_type'], $_REQUEST['new_privilege_entity_id'], $_REQUEST['new_privilege_read'], $_REQUEST['new_privilege_write']);
			}
			// Update Repository Record with Updated Privileges
			$privilege_json = $privilegeList->toJSON();
			if (!$repository->update(array('default_privileges_json' => $privilege_json))) $page->addError("Error updating privileges: " . $repository->error() . "\n");

			// Set record values
			if ($repository->error()) {
				$page->addError($repository->error());
				$page->success = null;

				// Keep form fields populated
				$form['code'] = $_REQUEST['code'];
				$form['name'] = $_REQUEST['name'];
				$form['type'] = $_REQUEST['type'];
				$form['status'] = $_REQUEST['status'];
				foreach ($metadata_keys as $key) $form[$key] = $_REQUEST[$key];
			} else {

				// Test Connection (only if we have required information)
				$should_test_connection = true;
				$connection_message = "";
				
				if ($repository->type == 's3') {
					$bucket = $repository->getMetadata('bucket');
					app_log("S3 Repository after reload - bucket metadata: '" . $bucket . "'", 'debug');
					app_log("S3 Repository after reload - all metadata keys: " . print_r($repository->getImpliedMetadataKeys(), true), 'debug');
					if (empty($bucket)) {
						$should_test_connection = false;
						$connection_message = "Connection test skipped - bucket name not provided";
					}
				}
				
				if ($should_test_connection) {
					if ($repository->connect()) {
						$page->appendSuccess("Connection tested successfully");
					} else {
						$page->addWarning("Connection test failed: " . $repository->error());
					}
				} else if (!empty($connection_message)) {
					$page->appendSuccess($connection_message);
				}

				// Populate Form Fields
				$form['code'] = $repository->code;
				$form['name'] = $repository->name;
				$form['type'] = $repository->type;
				$form['status'] = $repository->status;
				$form['path'] = $repository->getMetadata('path');
				$metadata_keys = $repository->getMetadataKeys();
				if (is_array($metadata_keys)) {
					foreach ($metadata_keys as $key) {
						$form[$key] = $repository->getMetadata($key);
					}
				}
			}
		} else {
			$form['code'] = $_REQUEST['code'];
			$form['name'] = $_REQUEST['name'];
			$form['type'] = $_REQUEST['type'];
			$form['status'] = $_REQUEST['status'];
			$metadata_keys = $repository->getImpliedMetadataKeys();
			if (is_array($metadata_keys)) {
				foreach ($metadata_keys as $key) {
					$form[$key] = $_REQUEST[$key];
				}
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
		$metadata_keys = $repository->getMetadataKeys();
		if (is_array($metadata_keys)) {
			foreach ($metadata_keys as $key) {
				$form[$key] = $repository->getMetadata($key);
			}
		}
		if (is_object($repository)) {
			$default_privileges = $repository->default_privileges();
			$override_privileges = $repository->override_privileges();
		} else {
			$page->addError("Repository not found or could not be created.");
		}
	} else {
		$form['type'] = 'local';
		$form['status'] = 'NEW';
		$form['code'] = '';
	}
}
// Repopulate Form with Request Data
elseif (!empty($_REQUEST['name'])) {
	$form['code'] = $_REQUEST['code'];
	$form['name'] = $_REQUEST['name'];
	$form['type'] = $_REQUEST['type'];
	$form['status'] = $_REQUEST['status'];
	$metadata_keys = $repository->getImpliedMetadataKeys();
	if (is_array($metadata_keys)) {
		foreach ($metadata_keys as $key) {
			$form[$key] = $_REQUEST[$key];
		}
	}
}

// Array of Type Metadata Keys
if (is_array($repository_types)) {
	foreach ($repository_types as $type => $name) {
		$repo = $factory->create($type);
		if (empty($repo)) continue;
		$keys = $repo->getImpliedMetadataKeys();
		$metadata_keys[$type] = $keys;
	}
}

// Get Default Privileges for Repository
if (!is_object($repository)) {
	$page->addError("Repository not found or could not be created.");
	$default_privileges = array();
} else {
	// Initialize privileges for both new and existing repositories
	$default_privileges = $repository->default_privileges();
	//$override_privileges = $repository->override_privileges();
}


	$page->title("Storage Repository");
	$page->setAdminMenuSection("Storage");  // Keep Storage section open
	if ($repository->id) $page->instructions = "Update values and click Submit to update repository setting";
	else $page->instructions = "Fill out form and click Submit to create a new Storage Repository";
$page->addBreadCrumb("Repositories", "/_storage/repositories");
if ($repository->id) $page->addBreadCrumb($repository->name, "/_storage/repository?id=" . $repository->id);
else $page->addBreadCrumb("New Repository");
