<?php
    ###############################################
    ### Handle API Request for package			###
    ### communications							###
    ### A. Caravello 4/13/2017					###
    ###############################################
	$_package = array(
		"name"		=> "package",
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
	elseif (! role('package manager')) {
		header("location: /_package/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
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
	### Add a Package								###
	###################################################
	function addPackage() {
		$response = new \HTTP\Response();
		if (! $GLOBALS['_SESSION_']->customer->has_role('package manager')) error("Permission Denied");

		# Identify Repository
		$repository = new \Storage\Repository();
		if (! $repository->get($_REQUEST['repository_code'])) error("Repository ".$_REQUEST['repository_code']." not found");
		app_log("Repository ".$repository->id);

		$package = new \Package\Package();
		if ($package->error) error("Error adding package: ".$package->error);
		$package->add(
			array(
				'code'				=> $_REQUEST['code'],
				'name'				=> $_REQUEST['name'],
				'description'		=> $_REQUEST['description'],
				'license'			=> $_REQUEST['license'],
				'status'			=> $_REQUEST['status'],
				'platform'			=> $_REQUEST['platform'],
				'repository_id'		=> $repository->id
			)
		);
		if ($package->error) error("Error adding package: ".$package->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->package = $package;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Update a Package							###
	###################################################
	function updatePackage() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('package manager')) error("Permission Denied");

		$package = new \Package\Package();
		if ($package->error) error("Error adding package: ".$package->error);
		$package->get($_REQUEST['code']);
		if ($package->error) app_error("Error finding package: ".$package->error,__FILE__,__LINE__);
		if (! $package->id) error("Package not found");

		$parameters = array();
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['license'])) $parameters['license'] = $_REQUEST['license'];
		if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		if (isset($_REQUEST['platform'])) $parameters['platform'] = $_REQUEST['platform'];
		if (isset($_REQUEST['repository_code']) && strlen($_REQUEST['repository_code'])) {
			# Identify Repository
			$repositorylist = new \Storage\RepositoryList();
			list($repository) = $repositorylist->find(array('code' => $_REQUEST['repository_code']));
			if (! $repository->id) {
				$this->error = "Repository not found";
				return false;
			}
			$parameters['repository_id'] = $_REQUEST['repository_id'];
		}

		$package->update($parameters);
		if ($package->error) app_error("Error updating package: ".$package->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->package = $package;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Find matching Packages						###
	###################################################
	function findPackages() {
		$packagelist = new \Package\PackageList();
		if (! $GLOBALS['_SESSION_']->customer->has_role('package manager')) error("Permission Denied");

		if ($packagelist->error) app_error("Error initializing packages: ".$packagelist->error,__FILE__,__LINE__);

		$parameters = array();
		if (isset($_REQUEST['code']) and strlen($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		if (isset($_REQUEST['platform'])) $parameters['platform'] = $_REQUEST['platform'];
		if (isset($_REQUEST['repository_code']) && strlen($_REQUEST['repository_code'])) {
			# Identify Repository
			$repositorylist = new \Storage\RepositoryList();
			list($repository) = $repositorylist->find(array('code' => $_REQUEST['repository_code']));
			if (! $repository->id) {
				$this->error = "Repository not found";
				return false;
			}
			$parameters['repository_id'] = $_REQUEST['repository_id'];
		}

	
		$packages = $packagelist->find($parameters);

		if ($packagelist->error) app_error("Error finding packages: ".$packagelist->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->package = $packages;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Add a Version								###
	###################################################
	function addVersion() {
		app_log("addVersion called");
		if (! $GLOBALS['_SESSION_']->customer->has_role('package manager')) error("Permission Denied");

        if ($_FILES['file']['error']) error("File upload failed: ".$_FILES['file']['error']);

		$package = new \Package\Package();
		$package->get($_REQUEST['package_code']);
		if ($package->error) error("Error finding package: ".$package->error);
		if (! $package->id) error("Package not found");

		$version = new \Package\Version();
		if ($version->error) error("Error adding version: ".$version->error);
		app_log(print_r($_FILES,true));
        if ($_FILES['file']['type']) {
            $mime_type = $_FILES['file']['type'];
        }
        elseif(guess_mime_type($_FILES['file']['name'])) {
            $mime_type = guess_mime_type($_FILES['file']['name']);
        }
        else error("Can't guess mime-type for ".$_FILES['file']['name']);

		$version->add(
			array(
				'package_id'	=> $package->id,
				'major'			=> $_REQUEST['major'],
				'minor'			=> $_REQUEST['minor'],
				'build'			=> $_REQUEST['build'],
				'status'		=> $_REQUEST['status'],
				'filename'		=> $_FILES['file']['name'],
				'path'			=> $_FILES['file']['tmp_name'],
                'size'          => $_FILES['file']['size'],
                'mime_type'     => $mime_type
			)
		);
		if ($version->error) error("Error adding version: ".$version->error);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Update a Version							###
	###################################################
	function updateVersion() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('package manager')) error("Permission Denied");

		$package = new \Package\Package();
		$package->get($_REQUEST['package_code']);
		if ($package->error) api_error("Error getting package: ".$package->error,'error',__FILE__,__LINE__);
		if (! $package->id) error("Package not found");

		$version = new \Package\Version();
		if ($version->error) error("Error adding version: ".$version->error);
		$version->get($package->id,$_REQUEST['major'],$_REQUEST['minor'],$_REQUEST['build']);
		if ($version->error) app_error("Error finding version: ".$version->error,__FILE__,__LINE__);
		if (! $version->id) error("Version not found");

		$parameters = array();
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

		$version->update($parameters);
		if ($version->error) app_error("Error updating version: ".$version->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Find matching Versions						###
	###################################################
	function findVersions() {
		$versionlist = new \Package\VersionList();
		if ($versionlist->error) app_error("Error initializing version list: ".$versionlist->error,__FILE__,__LINE__);

		$parameters = array();
		if (isset($_REQUEST['major']) and strlen($_REQUEST['major'])) $parameters['major'] = $_REQUEST['major'];
		if (isset($_REQUEST['minor']) and strlen($_REQUEST['minor'])) $parameters['minor'] = $_REQUEST['minor'];
		if (isset($_REQUEST['build']) and strlen($_REQUEST['build'])) $parameters['build'] = $_REQUEST['build'];
		if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
	
		$versions = $versionlist->find($parameters);

		if ($versionlist->error) app_error("Error finding versions: ".$versionlist->error,__FILE__,__LINE__);
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $versions;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Download Version							###
	###################################################
	function downloadVersion() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('package manager') && ! $GLOBALS['_SESSION_']->customer->has_role('monitor asset')) error("Permission Denied");

		$package = new \Package\Package();
		$package->get($_REQUEST['package_code']);
		if ($package->error) app_error("Error finding package: ".$package->error,__FILE__,__LINE__);
		if (! $package->id) error("Package not found");

		$version = new \Package\Version();
		$version->get($package->id,$_REQUEST['major'],$_REQUEST['minor'],$_REQUEST['build']);
		if ($version->error) app_error("Error finding version: ".$version->error,__FILE__,__LINE__);
		if (! $version->id) error("Version not found");

		$version->download();
	}

	###################################################
	### Latest Version								###
	###################################################
	function latestVersion() {
		$package = new \Package\Package();
		$package->get($_REQUEST['package_code']);
		if ($package->error) app_error("Error finding package: ".$package->error,__FILE__,__LINE__);
		if (! $package->id) error("Package not found");

		$version = $package->latestVersion();
		if ($package->error) app_error("Error finding version: ".$package->error,__FILE__,__LINE__);
		if (! $version->id) error("Version not found");

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;

		api_log($response);
		print formatOutput($response);
	}

	###################################################
	### Download Version							###
	###################################################
	function downloadLatestVersion() {
		if (! $GLOBALS['_SESSION_']->customer->has_role('package manager') && ! $GLOBALS['_SESSION_']->customer->has_role('monitor asset')) error("Permission Denied");

		$package = new \Package\Package();
		$package->get($_REQUEST['package_code']);
		if ($package->error) app_error("Error finding package: ".$package->error,__FILE__,__LINE__);
		if (! $package->id) error("Package not found");

		$version = new \Package\Version();
		$version->latest($package->id);
		if ($version->error) app_error("Error finding version: ".$version->error,__FILE__,__LINE__);
		if (! $version->id) error("Version not found");

		$version->download();
	}

	function schemaVersion() {
		$schema = new \Package\Schema();
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
		$schema = new \Package\Schema();
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
		if ($_REQUEST['_format'] == 'json') {
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
