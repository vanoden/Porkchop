<?php
    ###############################################
    ### Handle API Request for package			###
    ### communications							###
    ### A. Caravello 4/13/2017					###
    ###############################################
	$_package = array(
		"name"		=> "storage",
		"version"	=> "0.0.1",
		"release"	=> "2017-04-13"
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

	###############################################
	### Load API Objects						###
    ###############################################
	# Call Requested Event
	if (isset($_REQUEST["method"])) {
		$message = "Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code;
		if (array_key_exists('asset_code',$_REQUEST)) $message .= " for asset ".$_REQUEST['asset_code'];
		app_log($message,'debug',__FILE__,__LINE__);

		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! role('storage manager')) {
		header("location: /_storage/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage manager')) error('storage manager role required');
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;

		$_comm = new \Monitor\Communication();
		$_comm->update(json_encode($response));
		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add a Repository							###
	###################################################
	function addRepository() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage manager')) error('storage manager role required');
		$factory = new \Storage\RepositoryFactory();
		$repository = $factory->create($_REQUEST['type']);
		if ($factory->error) error("Error adding repository: ".$factory->error);
		$repository->add(
			array(
				'code'				=> $_REQUEST['code'],
				'name'				=> $_REQUEST['name'],
				'status'			=> $_REQUEST['status'],
				'path'				=> $_REQUEST['path']
			)
		);
		if ($repository->error) error("Error adding repository: ".$repository->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->repository = $repository;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Update a Repository							###
	###################################################
	function updateRepository() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage manager')) error('storage manager role required');
		$repository = new \Storage\Repository();
		if ($repository->error) error("Error adding repository: ".$repository->error);
		$repository->get($_REQUEST['code']);
		if ($repository->error) app_error("Error finding repository: ".$repository->error,__FILE__,__LINE__);
		if (! $repository->id) error("Repository not found");

		$parameters = array();
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

		$repository->update($parameters);
		if ($repository->error) app_error("Error updating repository: ".$repository->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->repository = $repository;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Find matching Repository					###
	###################################################
	function findRepositories() {
		$repositorylist = new \Storage\RepositoryList();
		if ($repositorylist->error) app_error("Error initializing repository list: ".$repositorylist->error,__FILE__,__LINE__);

		$parameters = array();
		if (isset($_REQUEST['code']) and strlen($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
	
		$repositories = $repositorylist->find($parameters);

		if ($repositorylist->error) app_error("Error finding repositories: ".$repositorylist->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->repository = $repositories;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Set Repository Metadata						###
	###################################################
	function setRepositoryMetadata() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage manager')) error('storage manager role required');
		$repositoryList = new \Storage\RepositoryList();
		list($repository) = $repositoryList->find(array("code" => $_REQUEST['code']));
		if ($repositoryList->error) app_error("Error finding repository: ".$repository->error,__FILE__,__LINE__);
		if (! $repository->id) error("Repository not found");

		$repository->setMetadata($_REQUEST['key'],$_REQUEST['value']);
		if ($repository->error) error($repository->error);

		$repository->get($_REQUEST['code']);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->repository = $repository;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Get Repository Metadata						###
	###################################################
	function getRepositoryMetadata() {
		$repository = new \Storage\Repository();
		if ($repository->error) app_error("Error initializing repository: ".$repository->error,__FILE__,__LINE__);

		$repository->get($_REQUEST['code']);
		if ($repository->error) app_error("Error finding repository: ".$repository->error,__FILE__,__LINE__);
		if (! $repository->id) error("Repository '".$_REQUEST['code']."' not found");

		$metadata = new \Storage\Repository\Metadata($repository->id,$_REQUEST['key']);
		if ($metadata->error) app_error("Error getting metadata: ".$metadata->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->metadata = $metadata;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add a File									###
	###################################################
	function addFile() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');

        $factory = new \Storage\RepositoryFactory();
        $repository = $factory->get($_REQUEST['repository_code']);
        if ($factory->error) error("Error loading repository: ".$factory->error);
        if (! $repository->id) error("Repository not found");
        app_log("Identified repo '".$repository->name."'");

		if (! $_REQUEST['name']) $_REQUEST['name'] = $_FILES['file']['name'];
		if (! file_exists($_FILES['file']['tmp_name'])) error("Temp file '".$_FILES['file']['tmp_name']."' not found");
		if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = $_FILES['file']['type'];
		if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = mime_content_type($_FILES['file']['name']);
		if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = guess_mime_type($_FILES['file']['name']);
		if (! $_REQUEST['mime_type']) error("mime_type not available for '".$_FILES['file']['name']."'");

        app_log("Storing ".$_REQUEST['name']." to ".$repository->path);
		if (isset($_REQUEST['read_protect']) && strlen($_REQUEST['read_protect']) && $_REQUEST['read_protect'] != 'NONE') {
			if ($repository->endpoint) {
				$this->error = "Can't protect a file in a repository with external endpoint";
				return false;
			}
		}

		# Check for Conflict
		$filelist = new \Storage\FileList();
		list($existing) = $filelist->find(
			array(
				'repository_id' => $repository->id,
				'name' => $_REQUEST['name'],
			)
		);
		if ($existing->id) error("File already exists with that name in repo ".$repository->name);

		# Add File to Library
		$file = new \Storage\File();
		if ($file->error) error("Error initializing file: ".$file->error);
		$file->add(
			array(
				'repository_id'		=> $repository->id,
				'name'				=> $_REQUEST['name'],
				'path'				=> $_REQUEST['path'],
				'mime_type'			=> $_REQUEST['mime_type'],
				'size'				=> $_FILES['file']['size'],
				'write_protect'		=> $_REQUEST['write_protect'],
				'read_protect'		=> $_REQUEST['read_protect']
			)
		);

		# Upload File Into Repository
		if ($file->error) error("Error adding file: ".$file->error);
		if (! $repository->addFile($file,$_FILES['file']['tmp_name'])) {
			$file->delete();
			error('Unable to add file to repository: '.$repository->error);
		}
        app_log("Stored file ".$file->id." at ".$repostory->path."/".$file->code);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->file = $file;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Update a File								###
	###################################################
	function updateFile() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');
		$file = new \Storage\File();
		if ($file->error) error("Error initializing file: ".$file->error);
		$file->get($_REQUEST['code']);
		if ($file->error) app_error("Error finding file: ".$file->error,__FILE__,__LINE__);
		if (! $file->id) error("File not found");

		$parameters = array();
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

		$file->update($parameters);
		if ($file->error) app_error("Error updating file: ".$file->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->file = $file;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Delete a File								###
	###################################################
	function deleteFile() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');
		$file = new \Storage\File();
		if ($file->error) error("Error initializing file: ".$file->error);
		$file->get($_REQUEST['code']);
		if ($file->error) app_error("Error finding file: ".$file->error,__FILE__,__LINE__);
		if (! $file->id) error("File not found");

		# Remove File From Repository
		if (! $file->repository->eraseFile($file)) {
			app_log("Failed to delete file ".$_REQUEST['code'].": ".$repository->error,'error',__FILE__,__LINE__);
		}

		$file->delete();
		if ($file->error) app_error("Error deleting file: ".$file->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Find matching file							###
	###################################################
	function findFiles() {
		$filelist = new \Storage\FileList();
		if ($filelist->error) app_error("Error initializing file list: ".$filelist->error,__FILE__,__LINE__);

		$parameters = array();
		if (isset($_REQUEST['code']) and strlen($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		if (isset($_REQUEST['repository_code']) && strlen($_REQUEST['repository_code'])) {
			$repositorylist = new \Storage\RepositoryList();
			list($repository) = $repositorylist->find(array('code' => $_REQUEST['repository_code']));
			if ($repositorylist->error) error("Error finding repository");
			if (! $repository->id) error("Repository not found");
			$parameters['repository_id'] = $repository->id;
		}
		$files = $filelist->find($parameters);

		if ($filelist->error) app_error("Error finding filelist: ".$filelist->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->file = $files;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Set File Metadata							###
	###################################################
	function setFileMetadata() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');
		$file = new \Storage\File();
		if ($file->error) app_error("Error initializing file: ".$file->error,__FILE__,__LINE__);

		$file->get($_REQUEST['code']);
		if ($file->error) app_error("Error finding file: ".$file->error,__FILE__,__LINE__);
		if (! $file->id) error("File '".$_REQUEST['code']."' not found");

		$file->setMetadata($_REQUEST['key'],$_REQUEST['value']);
		if ($file->error) app_error("Error setting metadata: ".$file->error,__FILE__,__LINE__);

		$file->get($_REQUEST['code']);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->file = $file;

		api_log($response);
		print formatOutput($response);
	}
	###################################################
	### Get File Metadata						###
	###################################################
	function getFileMetadata() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');
		$file = new \Storage\File();
		if ($file->error) app_error("Error initializing file: ".$file->error,__FILE__,__LINE__);

		$file->get($_REQUEST['code']);
		if ($file->error) app_error("Error finding file: ".$file->error,__FILE__,__LINE__);
		if (! $file->id) error("File '".$_REQUEST['code']."' not found");

		$metadata = new \Storage\File\Metadata($file->id,$_REQUEST['key']);
		if ($metadata->error) app_error("Error getting metadata: ".$metadata->error,__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->metadata = $metadata;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Download matching file						###
	###################################################
	function downloadFile() {
		$file = new \Storage\File();
		$file->get($_REQUEST['code']);
		if ($file->error) app_error("Error getting file: ".$file->error,__FILE__,__LINE__);
		if (! $file->id) error("File not found");
		$file->repository->retrieveFile($file);
		if ($file->error) app_error("Error getting file: ".$file->error,__FILE__,__LINE__);
	}
	function schemaVersion() {
		$schema = new \Storage\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	function schemaUpgrade() {
		$schema = new \Storage\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	###################################################
	### System Time									###
	###################################################
	function system_time() {
		return date("Y-m-d H:i:s");
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		$response = new \HTTP\Response();
		$response->error = $message;
		$response->success = 0;
		api_log($response);
		print formatOutput($response);
		exit;
	}

	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		}
		else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}
?>
