<?php
	namespace Package;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'package';
			$this->_version = '0.1.1';
			$this->_release = '2020-06-03';
			$this->_schema = new Schema();
			$this->_admin_role = 'package manager';
			parent::__construct();
		}

		###################################################
		### Add a Package								###
		###################################################
		public function addPackage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$response = new \HTTP\Response();
			if (! $GLOBALS['_SESSION_']->customer->can('manage packages')) $this->error("Permission Denied");
	
			if (! isset($_REQUEST['code'])) $this->error("unique code field required for addPackage");

			# Identify Repository
			$repository = new \Storage\Repository();
			if (! $repository->get($_REQUEST['repository_code'])) $this->error("Repository ".$_REQUEST['repository_code']." not found");
			app_log("Repository ".$repository->id);
	
			$package = new \Package\Package();
			if ($package->error()) $this->error("Error adding package: ".$package->error());
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
			if ($package->error()) $this->error("Error adding package: ".$package->error());

            $response = new \APIResponse();
            $response->addElement('package',$package);
            $response->print();
		}

		###################################################
		### Update a Package							###
		###################################################
		public function updatePackage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage packages')) $this->error("Permission Denied");
	
			$package = new \Package\Package();
			if ($package->error()) error("Error adding package: ".$package->error());
			$package->get($_REQUEST['code']);
			if ($package->error()) $this->app_error("Error finding package: ".$package->error(),__FILE__,__LINE__);
			if (! $package->id) $this->error("Package not found");
	
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
					$this->error("Repository not found");
					return false;
				}
				$parameters['repository_id'] = $_REQUEST['repository_id'];
			}
	
			$package->update($parameters);
			if ($package->error()) $this->app_error("Error updating package: ".$package->error(),__FILE__,__LINE__);

            $response = new \APIResponse();
            $response->addElement('package',$package);
            $response->print();
		}

		###################################################
		### Get A Package by Code						###
		###################################################
		public function getPackage() {
			$package = new \Package\Package();
			if ($package->get($_REQUEST['code'])) {
                $response = new \APIResponse();
                $response->addElement('package',$package);
                $response->print();
			}
			else {
				$this->error("Package not found");
			}
		}
	
		###################################################
		### Find matching Packages						###
		###################################################
		public function findPackages() {
			$packagelist = new \Package\PackageList();
			if (! $GLOBALS['_SESSION_']->customer->can('use package module')) $this->error("Permission Denied");
	
			if ($packagelist->error()) app_error("Error initializing packages: ".$packagelist->error(),__FILE__,__LINE__);
	
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
					$this->error("Repository not found");
					return false;
				}
				$parameters['repository_id'] = $_REQUEST['repository_id'];
			}
	
		
			$packages = $packagelist->find($parameters);
	
			if ($packagelist->error()) app_error("Error finding packages: ".$packagelist->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('package',$packages);
			$response->print();
		}

		###################################################
		### Add a Version								###
		###################################################
		public function addVersion() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			app_log("addVersion called");
			if (! $GLOBALS['_SESSION_']->customer->can('add package versions')) $this->error("Permission Denied");
	
			if ($_FILES['file']['error']) $this->error("File upload failed: ".$_FILES['file']['error']);
	
			$package = new \Package\Package();
			$package->get($_REQUEST['package_code']);
			if ($package->error()) $this->error("Error finding package: ".$package->error());
			if (! $package->id) $this->error("Package not found");

			//app_log(print_r($_FILES,true));
			if ($_FILES['file']['type']) {
				$mime_type = $_FILES['file']['type'];
			} elseif(guess_mime_type($_FILES['file']['name'])) {
				$mime_type = guess_mime_type($_FILES['file']['name']);
			} else $this->error("Can't guess mime-type for ".$_FILES['file']['name']);
	
			$version = $package->addVersion(
				array(
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
			if ($package->error()) $this->error("Error adding version: ".$package->error());
            if (empty($version)) $this->error("Unhandled exception");

			$response = new \APIResponse();
			$response->addElement('version',$version);
			$response->print();
		}

		###################################################
		### Update a Version							###
		###################################################
		public function updateVersion() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('upload packages')) $this->error("Permission Denied");
	
			$package = new \Package\Package();
			$package->get($_REQUEST['package_code']);
			if ($package->error()) $this->app_error("Error getting package: ".$package->error(),'error',__FILE__,__LINE__);
			if (! $package->id) $this->error("Package not found");
	
			$version = new \Package\Version();
			if ($version->error) $this->error("Error adding version: ".$version->error);
			$version->get($package->id,$_REQUEST['major'],$_REQUEST['minor'],$_REQUEST['build']);
			if ($version->error) app_error("Error finding version: ".$version->error,__FILE__,__LINE__);
			if (! $version->id) $this->error("Version not found");
	
			$parameters = array();
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
	
			$version->update($parameters);
			if ($version->error) $this->app_error("Error updating version: ".$version->error,__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('version',$version);
			$response->print();
		}

		###################################################
		### Find matching Versions						###
		###################################################
		public function findVersions() {
			$versionlist = new \Package\VersionList();
			if ($versionlist->error()) $this->app_error("Error initializing version list: ".$versionlist->error(),__FILE__,__LINE__);
	
			$parameters = array();
			if (isset($_REQUEST['major']) and strlen($_REQUEST['major'])) $parameters['major'] = $_REQUEST['major'];
			if (isset($_REQUEST['minor']) and strlen($_REQUEST['minor'])) $parameters['minor'] = $_REQUEST['minor'];
			if (isset($_REQUEST['build']) and strlen($_REQUEST['build'])) $parameters['build'] = $_REQUEST['build'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

			$versions = $versionlist->find($parameters);
	
			if ($versionlist->error()) $this->app_error("Error finding versions: ".$versionlist->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('version',$versions);
			$response->print();
		}
	
		###################################################
		### Download Version							###
		###################################################
		public function downloadVersion() {
			$package = new \Package\Package();
			$package->get($_REQUEST['package_code']);
			if ($package->error()) $this->app_error("Error finding package: ".$package->error(),__FILE__,__LINE__);
			if (! $package->id) $this->error("Package not found");

			$version = new \Package\Version();
			$version->get($package->id,$_REQUEST['major'],$_REQUEST['minor'],$_REQUEST['build']);
			if ($version->error) $this->app_error("Error finding version: ".$version->error,__FILE__,__LINE__);
			if (! $version->id) $this->error("Version not found");

			if (! $version->readable($GLOBALS['_SESSION_']->customer->id)) $this->error("Permission Denied");
			$file = $version->file();
			$file->download();
		}

		###################################################
		### Latest Version								###
		###################################################
		public function latestVersion() {
			$package = new \Package\Package();
			$package->get($_REQUEST['package_code']);
			if ($package->error()) $this->app_error("Error finding package: ".$package->error(),__FILE__,__LINE__);
			if (! $package->id) $this->error("Package not found");

			$version = $package->latestVersion();
			if ($package->error()) $this->app_error("Error finding version: ".$package->error(),__FILE__,__LINE__);
			if (! $version->id) $this->error("Version not found");

			$response = new \APIResponse();
			$response->addElement('version',$version);
			$response->print();
		}

		###################################################
		### Download Version							###
		###################################################
		public function downloadLatestVersion() {
			$package = new \Package\Package();
			$package->get($_REQUEST['package_code']);
			if ($package->error()) $this->app_error("Error finding package: ".$package->error(),__FILE__,__LINE__);
			if (! $package->id) $this->error("Package not found");

			if (! $package->readable($GLOBALS['_SESSION_']->customer->id)) $this->error("Permission Denied");
	
			$version = new \Package\Version();
			$version->latest($package->id);
			if ($version->error) $this->app_error("Error finding version: ".$version->error,__FILE__,__LINE__);
			if (! $version->id) $this->error("Version not found");
	
			$file = $version->file();
			$file->download();
		}
		
		public function _methods() {
			return array(
				'ping'	=> array(),
				'addPackage'	=> array(
					'description'	=> 'Add a new package',
					'privilege_required'	=> 'manage packages',
					'token_required'	=> true,
					'return_element'	=> 'package',
					'return_type'		=> 'Package::Package',
					'parameters'		=> array(
						'code'			=> array(
							'required' => true,
							'validation_method' => 'Package::Package::validCode()',
						),
						'name'			=> array(
							'required' => true,
							'validation_method' => 'Package::Package::validName()',
						),
						'description'	=> array(),
						'license'		=> array(),
						'platform'		=> array(),
						'repository_code'	=> array(
							'required' => true,
							'validation_method' => 'Storage::Repository::validCode()',
						),
						'status'		=> array()
					)
				),
				'updatePackage'	=> array(
					'description'	=> 'Update an existing package',
					'privilege_required'	=> 'manage packages',
					'token_required'	=> true,
					'return_element'	=> 'package',
					'return_type'		=> 'Package::Package',
					'parameters'		=> array(
						'code'			=> array('required' => true),
						'name'			=> array(),
						'description'	=> array(),
						'license'		=> array(),
						'platform'		=> array(),
						'status'		=> array(),
						'repository_code'	=> array()
					)
				),
				'getPackage'	=> array(
					'description'	=> 'Get a package by code',
					'privilege_required'	=> 'use package module',
					'return_element'	=> 'package',
					'return_type'		=> 'Package::Package',
					'parameters'		=> array(
						'code'	=> array('required' => true),
					)
				),
				'findPackages'	=> array(
					'description'	=> 'Find matching packages',
					'privilege_required'	=> 'use package module',
					'return_element'	=> 'package',
					'return_type'		=> 'Package::Package',
					'parameters'		=> array(
						'name'			=> array(),
						'platform'		=> array(),
						'repository_code'	=> array(),
						'status'		=> array(),
					)
				),
				'addVersion'	=> array(
					'package_code'	=> array('required' => true),
					'major'			=> array('required' => true),
					'minor'			=> array('required' => true),
					'build'			=> array('required' => true),
					'status'		=> array(),
					'file'			=> array('type'	=> 'file', 'required' => true)
				),
				'updateVersion'	=> array(
					'package_code'	=> array('required' => true),
					'status'		=> array()
				),
				'findVersions'	=> array(
					'package_code'	=> array(),
					'major'			=> array(),
					'minor'			=> array(),
					'build'			=> array(),
					'status'		=> array()
				),
				'downloadVersion'	=> array(
					'package_code'	=> array('required' => true),
					'major'			=> array('required' => true),
					'minor'			=> array('required' => true),
					'build'			=> array('required' => true),
				),
				'latestVersion'	=> array(
					'package_code'	=> array('required' => true),
				),
				'downloadLatestVersion'	=> array(
					'package_code'	=> array('required' => true),
				),
			);
		}
	}
