<?php
	namespace Storage;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'storage';
			$this->_version = '0.2.0';
			$this->_release = '2021-08-11';
			$this->_schema = new \Storage\Schema();
			$this->_admin_role = 'storage manager';
			parent::__construct();
		}

		###################################################
		### Add a Repository							###
		###################################################
		public function addRepository() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage storage repositories')) error('storage manager role required');
			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->create($_REQUEST['type']);
			if ($factory->error()) $this->error("Error adding repository: ".$factory->error());
			$repository->add(
				array(
					'code'				=> $_REQUEST['code'],
					'name'				=> $_REQUEST['name'],
					'status'			=> $_REQUEST['status'],
					'path'				=> $_REQUEST['path']
				)
			);
			if ($repository->error()) $this->error("Error adding repository: ".$repository->error());

			$response = new \APIResponse();
			$response->addElement('repository',$repository);
			$response->print();
		}

		###################################################
		### Update a Repository							###
		###################################################
		public function updateRepository() {
			// Validate AntiCSRF Token
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			// Check for Session User's Authorization
			if (! $GLOBALS['_SESSION_']->customer->can('manage storage repositories')) error('storage manager role required');

			// Fetch Specified Repository
			$repository = new \Storage\Repository();
			if ($repository->error()) $this->error("Error adding repository: ".$repository->error());
			$repository->get($_REQUEST['code']);
			if ($repository->error()) $this->app_error("Error finding repository: ".$repository->error(),__FILE__,__LINE__);
			if (! $repository->id) $this->error("Repository not found");

			// Populate Update Parameters
			$parameters = array();
			if (!empty($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			if (!empty($_REQUEST['status']) && $_REQUEST['status'] != '[NULL]') $parameters['status'] = $_REQUEST['status'];

			// Update Repository
			$repository->update($parameters);
			if ($repository->error()) $this->app_error("Error updating repository: ".$repository->error(),__FILE__,__LINE__);

			// Generate Formatted Response
			$response = new \APIResponse();
			$response->addElement('repository',$repository);
			$response->print();
		}
	
		###################################################
		### Find matching Repository					###
		###################################################
		public function findRepositories() {
			$repositorylist = new \Storage\RepositoryList();
			if ($repositorylist->error()) app_error("Error initializing repository list: ".$repositorylist->error(),__FILE__,__LINE__);

			$parameters = array();
			if (isset($_REQUEST['code']) and strlen($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
		
			$repositories = $repositorylist->find($parameters);
			$shownRepositories = array();
			foreach ($repositories as $repository) {
				if (get_class($repository) == 'Storage\Repository\S3') {
					$repository->unsetAWS();      
				}
				array_push($shownRepositories,$repository);
			}
			if ($repositorylist->error()) app_error("Error finding repositories: ".$repositorylist->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('repository',$shownRepositories);
			$response->print();
		}
		
		###################################################
		### Set Repository Metadata						###
		###################################################
		public function setRepositoryMetadata() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage storage repositories')) error('storage manager role required');
			$repositoryList = new \Storage\RepositoryList();
			list($repository) = $repositoryList->find(array("code" => $_REQUEST['code']));
			if ($repositoryList->error()) $this->app_error("Error finding repository: ".$repository->error(),__FILE__,__LINE__);
			if (! $repository->id) $this->error("Repository not found");

			$repository->setMetadata($_REQUEST['key'],$_REQUEST['value']);
			if ($repository->error()) $this->error($repository->error());

			$repository->get($_REQUEST['code']);

			$response = new \APIResponse();
			$response->addElement('repository',$repository);

			api_log($response);
			$response->print();
		}
		
		###################################################
		### Get Repository Metadata						###
		###################################################
		public function getRepositoryMetadata() {
			$repository = new \Storage\Repository();
			if ($repository->error()) $this->app_error("Error initializing repository: ".$repository->error(),__FILE__,__LINE__);

			$repository->get($_REQUEST['code']);
			if ($repository->error()) $this->app_error("Error finding repository: ".$repository->error(),__FILE__,__LINE__);
			if (! $repository->id) $this->error("Repository '".$_REQUEST['code']."' not found");

			$metadata = $repository->getMetadata($_REQUEST['key']);
			if ($repository->error()) $this->app_error("Error getting metadata: ".$repository->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('metadata',$metadata);
			$response->print();
		}

		###################################################
		### Add a File									###
		###################################################
		public function addFile() {
			// Check for CSRF Token
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			// Find Specified Repository
			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->get($_REQUEST['repository_code']);
			if ($factory->error()) $this->error("Error loading repository: ".$factory->error());
			if (! $repository->id) $this->error("Repository not found");
			app_log("Identified repo '".$repository->name."'");

			// Pull Parameters from Form
			if (! $_REQUEST['name']) $_REQUEST['name'] = $_FILES['file']['name'];
			if (! file_exists($_FILES['file']['tmp_name'])) $this->error("Temp file '".$_FILES['file']['tmp_name']."' not found");
			if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = $_FILES['file']['type'];
			if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = mime_content_type($_FILES['file']['name']);
			if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = guess_mime_type($_FILES['file']['name']);
			if (! $_REQUEST['mime_type']) $this->error("mime_type not available for '".$_FILES['file']['name']."'");

			// Check for Privileges
			if (! $repository->writable()) $this->error("Permission denied");

			// Check for File Name Conflict within repository
			$filelist = new \Storage\FileList();
			list($existing) = $filelist->find(
				array(
					'repository_id' => $repository->id,
					'path'			=> $_REQUEST['path'],
					'name'			=> $_REQUEST['name']
				)
			);
			if ($existing->id) error("File already exists with that name in repo ".$repository->name);

			// Add File to Database
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
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

			// Upload File Into Repository
			if ($file->error()) $this->error("Error adding file: ".$file->error());
			if (! $repository->addFile($file,$_FILES['file']['tmp_name'])) {
				// Remove file from database if upload to repository failed
				$file->delete();
				$this->error('Unable to add file to repository: '.$repository->error());
			}
			app_log("Stored file ".$file->id." at ".$repository->path()."/".$file->code);

			$response = new \APIResponse();
			$response->addElement('file',$file);
			$response->print();
		}

		###################################################
		### Update a File								###
		###################################################
		public function updateFile() {
			// Check for CSRF Token
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			// Find Specified File
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");

			// Check Privileges
			if (! $file->writable()) $this->error("Permission denied");

			// Prepare Update Parameters
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

			// Update File
			$file->update($parameters);
			if ($file->error()) $this->app_error("Error updating file: ".$file->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('file',$file);
			$response->print();
		}

		###################################################
		### Delete a File								###
		###################################################
		public function deleteFile() {
			// Check for CSRF Token
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			// Find Specified File
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->notFound("File not found");

			// Check Privileges
			if (! $file->writable()) $this->error("Permission denied");

			// Remove File From Repository
			$repository = $file->repository();
			if (! $file->repository()->eraseFile($file)) $this->app_error("Failed to delete file ".$_REQUEST['code'].": ".$repository->error());

			// Remove Record from database
			$file->delete();
			if ($file->error()) $this->app_error("Error deleting file: ".$file->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->print();
		}

		###################################################
		### Is User Permitted to Read File			###
		###################################################
		public function readPermitted() {
			// Find Specified File
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['file_code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->notFound("File not found");

			// Find Specified User
			if (! empty($_REQUEST['user_code'])) {
				$user = new \Register\Customer();
				if ($user->get($_REQUEST['user_code'])) {
					$user_id = $user->id;
				}
				else {
					$this->error("User not found");
				}
			}
			// Default to session user if not specified
			else {
				$user_id = $GLOBALS['_SESSION_']->customer->id;
			}

			// Respond with Permission Status
			$response = new \APIResponse();
			if ($file->readable($user_id)) $response->addElement('permitted',1);
			else $response->addElement('permitted',0);
			$response->print();
		}

		###################################################
		### Is User Permitted to Update File			###
		###################################################
		public function writePermitted() {
			// Find Specified File
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['file_code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");

			// Find Specified User
			if (! empty($_REQUEST['user_code'])) {
				$user = new \Register\Customer();
				if ($user->get($_REQUEST['user_code'])) {
					$user_id = $user->id;
				}
				else {
					$this->notFound("User not found");
				}
			}
			// Default to session user if not specified
			else {
				$user_id = $GLOBALS['_SESSION_']->customer->id;
			}

			// Respond with Permission Status
			$response = new \APIResponse();
			if ($file->writePermitted($user_id)) $response->addElement('permitted',1);
			else $response->addElement('permitted',0);
			$response->print();
		}

		###################################################
		### Get Privileges for a File					###
		###################################################
		public function getFilePrivileges() {
			// Find Specified File
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			if (!empty($_REQUEST['code'])) {
				if (! $file->validCode($_REQUEST['code'])) $this->error("Invalid file code");
				$file->get($_REQUEST['code']);
			}
			else if (! empty($_REQUEST['id'])) {
				if (! $file->validID($_REQUEST['id'])) $this->error("Invalid file id");
				$file->get($_REQUEST['id']);
			}
			else {
				if (empty($_REQUEST['repository_code'])) {
					$this->error("Must specify code or repository_code and path and name");
				}
				$repositoryFactory = new \Storage\RepositoryFactory();
				$repository = $repositoryFactory->get($_REQUEST['repository_code']);
				if ($repository->error()) $this->error("Error loading repository: ".$repository->error());
				if (!empty($repository->id) && !empty($_REQUEST['path']) && !empty($_REQUEST['name'])) {
					$file->fromPathName($repository->id,$_REQUEST['path'],$_REQUEST['name']);
				}
				else $this->error("Must specify code or path and name");
				if (! $file->validPath($_REQUEST['path'])) {
					$this->error("Invalid path");
				}
			}
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->exists()) $this->notFound("File ".$_REQUEST['name']." not found at path ".$_REQUEST['path']." in repository ".$repository->name);
			if (! $file->readable() && ! $GLOBALS['_SESSION_']->customer->can('manage storage files')) $this->error('permission denied');

			// Get Privilege Settings for File
			$privileges = $file->getPrivileges();
			$document = array();
			foreach ($privileges as $privilege) {
				$perms = "";
				if ($privilege->read) $perms .= "r";
				if ($privilege->write) $perms .= "w";
				$document["entity"][$privilege->entity_type_name()][$privilege->entity_code()]['name'] = $privilege->entity_name();
				$document["entity"][$privilege->entity_type_name()][$privilege->entity_code()]['can'] = $perms;
			}

			$response = new \APIResponse();
			$response->addElement('privilege',$document);
			$response->print();
		}

		###################################################
		### Update Privileges for a File				###
		###################################################
		public function updateFilePrivileges() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['file_code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");
			if ($file->user()->id != $GLOBALS['_SESSION_']->customer->id && ! $GLOBALS['_SESSION_']->customer->can('update storage file permissions')) error('permission denied');

			if (empty($_REQUEST['entity_type']) || !preg_match('/^[uora]/i',$_REQUEST['entity_type'])) $this->error("entity_type must be 'user','organization', 'role' or 'all'");
			if (empty($_REQUEST['mask']) || !preg_match('/^[\-\+wrgf]+/i',$_REQUEST['mask'])) $this->error("mask must be some combination of signs (+/-) and letters 'wrgf'");

			$mask = array();
			if ($_REQUEST['mask'] == '+f') $mask = array('+w','+r','+g');
			elseif ($_REQUEST['mask'] == '-f') $mask = array('-w','-r','-g');
			else {
				$neg = '+';
				while (strlen($_REQUEST['mask']) > 0) {
					if (preg_match('/^[\-\+]/',$_REQUEST['mask'])) $neg = substr($_REQUEST['mask'],0,1);
					else {
						array_push($mask,$neg.substr($_REQUEST['mask'],0,1));
						$neg = '+';
					}
					$_REQUEST['mask'] = substr($_REQUEST['mask'],1);
				}
			}
			if (preg_match('/^u/i',$_REQUEST['entity_type'])) {
				if (isset($_REQUEST['entity_id'])) {
					$user = new \Register\Person($_REQUEST['entity_id']);
				}
				elseif(isset($_REQUEST['entity_code'])) {
					$user = new \Register\Person();
					if (! $user->get($_REQUEST['entity_code'])) $this->error("User not found");
				}
				else $this->error("Must identify user");
				if (! $user->id) $this->error("User not found");
				if (! $file->updatePrivileges("u",$user->id,$mask)) $this->error("Error updating privileges: ".$file->error());
			}
			elseif (preg_match('/^o/i',$_REQUEST['entity_type'])) {
				if (isset($_REQUEST['entity_id'])) {
					$organization = new \Register\Organization($_REQUEST['entity_id']);
				}
				elseif(isset($_REQUEST['entity_code'])) {
					$organization = new \Register\Organization();
					if (! $organization->get($_REQUEST['entity_code'])) $this->error("Organization not found");
				}
				else $this->error("Must identify organization");
				if (! $organization->id) $this->error("Organization not found");
				if (! $file->updatePrivileges("o",$organization->id,$mask)) $this->error("Error updating privileges: ".$file->error());
			}
			elseif (preg_match('/^r/i',$_REQUEST['entity_type'])) {
				if (isset($_REQUEST['entity_id'])) {
					$role = new \Register\Role($_REQUEST['entity_id']);
				}
				elseif(isset($_REQUEST['entity_code'])) {
					$role = new \Register\Role();
					if (! $role->get($_REQUEST['entity_code'])) $this->error("Role not found");
				}
				else $this->error("Must identify role");
				if (! $role->id) $this->error("Role not found");
				if (! $file->updatePrivileges("r",$role->id,$_REQUEST['mask'])) $this->error("Error updating privileges: ".$file->error());
			}
			elseif (preg_match('/^a/i',$_REQUEST['entity_type'])) {
				if (! $file->updatePrivileges("a",null,$mask)) $this->error("Error updating privileges: ".$file->error());
			}

			$response = new \APIResponse();
			$response->print();
		}

		###################################################
		### Find matching file							###
		###################################################
		public function findFiles() {
			$filelist = new \Storage\FileList();
			if ($filelist->error()) $this->app_error("Error initializing file list: ".$filelist->error(),__FILE__,__LINE__);

			$parameters = array();
			if (isset($_REQUEST['code']) and strlen($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
			if (isset($_REQUEST['repository_code']) && strlen($_REQUEST['repository_code'])) {
				$repositorylist = new \Storage\RepositoryList();
				list($repository) = $repositorylist->find(array('code' => $_REQUEST['repository_code']));
				if ($repositorylist->error()) $this->error("Error finding repository");
				if (! $repository->id) $this->error("Repository not found");
				$parameters['repository_id'] = $repository->id;
			}
			$files = $filelist->find($parameters);

			if ($filelist->error()) $this->app_error("Error finding filelist: ".$filelist->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('file',$files);
			$response->print();
		}
		
		###################################################
		### Set File Metadata							###
		###################################################
		public function setFileMetadata() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage storage files')) error('storage upload role required');
			$file = new \Storage\File();
			if ($file->error()) $this->app_error("Error initializing file: ".$file->error(),__FILE__,__LINE__);

			$file->get($_REQUEST['code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->notFound("File not found");

			$file->setMetadata($_REQUEST['key'],$_REQUEST['value']);
			if ($file->error()) $this->app_error("Error setting metadata: ".$file->error(),__FILE__,__LINE__);

			$file->get($_REQUEST['code']);

			$response = new \APIResponse();
			$response->addElement('file',$file);
			$response->print();
		}
		
		###################################################
		### Get File Metadata						    ###
		###################################################
		public function getFileMetadata() {
			if (! $GLOBALS['_SESSION_']->customer->can('manage storage files')) error('storage upload role required');
			$file = new \Storage\File();
			if ($file->error()) $this->app_error("Error initializing file: ".$file->error(),__FILE__,__LINE__);

			$file->get($_REQUEST['code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->error("File '".$_REQUEST['code']."' not found");

			$metadata = $file->getMetadata($_REQUEST['key']);
			if ($file->error()) $this->app_error("Error getting metadata: ".$file->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('metadata',$metadata);
			$response->print();
		}

		###################################################
		### Download matching file						###
		###################################################
		public function downloadFile() {
			$file = new \Storage\File();
			$file->get($_REQUEST['code']);
			if ($file->error()) $this->app_error("Error getting file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");
			$file->repository()->retrieveFile($file);
			if ($file->error()) $this->app_error("Error getting file: ".$file->error(),__FILE__,__LINE__);
		}

		###################################################
		### Set Empty Paths to Root						###
		###################################################
		public function rootPath() {
			$count = 0;
			$repoList = new \Storage\RepositoryList();
			$repos = $repoList->find();
			foreach ($repos as $repo) {
				$files = $repo->files();
				foreach ($files as $file) {
					if (! preg_match('/^\//',$file->path())) {
						$file->update(array('path' => '/'.$file->path()));
						$count ++;
					}
				}
			}
			$response = new \APIResponse();
			$response->addElement('count',$count);
			$response->print();
		}

		public function _methods() {
			$porkchop = new \Porkchop();
			return array(
				'ping'			=> array(
					'description'	=> "Simple ping method to test connectivity",
					'parameters'	=> array()
				),
				'addRepository'	=> array(
					'description'	=> "Add a new storage repository",
					'privilege_required'	=> 'manage storage repositories',
					'token_required'		=> true,
					'parameters'		=> array(
						'code'		=> array(
							'default'			=> $porkchop->biguuid(),
							'object'			=> "repository",
							'property'			=> "code",
							'description'		=> "unique code to identify a repository",
							'type'				=> 'text',
							'content-type'		=> 'string'
						),
						'type'		=> array(
							'object'			=> "repository",
							'property'			=> "type",
							'description'		=> "type of repository",
							'options'			=> array(
								'local'		=> "local",
								's3'		=> "s3"
							),
							'required' => true
						),
						'name'		=> array(
							'object'			=> "repository",
							'property'			=> "name",
							'description'		=> "Name of Repository",
							'type'				=> "text",
							'prompt'			=> "new repository name",
							'required' => true
						),
						'status'	=> array(
							'object'			=> "repository",
							'property'			=> "status",
							'description'		=> "Status of Repository",
							'type'				=> "text",
							'options'			=> array(
								'NEW'		=> "NEW",
								'ACTIVE'	=> "ACTIVE",
								'INACTIVE'	=> "INACTIVE"
							),
							'required' => true
						)
					),
				),
				'updateRepository'	=> array(
					'description'	=> "Update an existing repository",
					'parameters'	=> array(
						'code'		=> array(
							'object'			=> "repository",
							'property'			=> "code",
							'description'		=> "unique code to identify a repository",
							'type'				=> 'text',
							'required'			=> true
						),
						'name'		=> array(
							'object'			=> "repository",
							'property'			=> "name",
							'description'		=> "New name of repository",
							'type'				=> "text",
							'prompt'			=> "new repository name"
						),
						'status'	=> array(
							'object'			=> "repository",
							'property'			=> "status",
							'description'		=> "New status of repository",
							'type'				=> "text",
							'options'			=> array(
								'[NULL]'	=> "unchanged",
								'NEW'		=> "NEW",
								'ACTIVE'	=> "ACTIVE",
								'INACTIVE'	=> "INACTIVE"
							)
						)
					)
				),
				'findRepositories'	=> array(
					'description'	=> "Find matching repositories",
					'parameters'	=> array(
						'code'		=> array(
							'object'			=> "repository",
							'property'			=> "code",
							'description'		=> "Unique code for repository"
						),
						'name'		=> array(
							'object'			=> "repository",
							'property'			=> "name",
							'description'		=> "Name of repository"
						),
						'status'	=> array(
							'object'			=> "repository",
							'property'			=> "status",
							'description'		=> "Status of repository",
							'options'			=> array(
								'[NULL]'	=> 'any',
								'NEW'		=> "NEW",
								'ACTIVE'	=> "ACTIVE",
								'INACTIVE'	=> "INACTIVE"
							)
						),
						'type'		=> array(
							'object'			=> "repository",
							'property'			=> "type",
							'description'		=> "Type of repository",
							'options'			=> array(
								'[NULL]'	=> "any",
								'local'		=> "local",
								's3'		=> "s3"
							)
						)
					)
				),
				'setRepositoryMetadata' => array(
					'description'	=> "Set metadata for a repository",
					'authentication_required'	=> true,
					'token_required'			=> true,
					'parameters'	=> array(
						'code'		=> array(
							'object'			=> "repository",
							'property'			=> "code",
							'type'				=> "string",
							'description'		=> "Unique code for repository",
							'required'			=> true
						),
						'key'		=> array(
							'object'			=> "metadata",
							'property'			=> "key",
							'type'				=> "string",
							'description'		=> "Key for metadata",
							'required'			=> true
						),
						'value'		=> array(
							'object'			=> "metadata",
							'property'			=> "value",
							'type'				=> "string",
							'description'		=> "Value for metadata",
							'required'			=> true
						)
					)
				),
				'getRepositoryMetadata' => array(
					'description'	=> "Get metadata for a repository",
					'authentication_required'	=> true,
					'parameters'	=> array(
						'code'		=> array(
							'object'			=> "repository",
							'property'			=> "code",
							'type'				=> "string",
							'description'		=> "Unique code for repository",
							'required'			=> true
						),
						'key'		=> array(
							'object'			=> "metadata",
							'property'			=> "key",
							'type'				=> "string",
							'description'		=> "Key for metadata",
							'required'			=> true
						)
					)
				),
				'addFile' => array(
					'description'	=> "Add a new file to a repository",
					'authentication_required'	=> true,
					'token_required'			=> true,
					'parameters'		=> array(
						'repository_code'	=> array(
							'required' => true,
							'description' => "Unique code for repository",
							'validation_method' => "Storage::Repository::validCode()"
						),
						'name'		=> array(
							'description'	=> "Name of file",
							'required'		=> false,
							'validation_method' => "Storage::File::validName()"
						),
						'mime_type'	=> array(
							'description'	=> "MIME type of file",
							'required'		=> false,
							'validation_method' => "Storage::File::validMimeType()"
						),
						'file'		=> array(
							'description' => "File to upload",
							'required' => true,
							'content_type'	=> "file",
							'type'			=> "file"
						),
					)
				),
				'updateFile' => array(
					'description'	=> "Update an existing file",
					'authentication_required'	=> true,
					'token_required'			=> true,
					'parameters'	=> array(
						'code'		=> array(
							'description'	=> "Unique code for file",
							'required' => true,
							'validation_method' => "Storage::File::validCode()"
						),
						'name'		=> array(
							'description'	=> "New name for file",
							'required' => false,
							'validation_method' => "Storage::File::validName()"
						),
						'status'	=> array(
							'description'	=> "New status for file",
							'required' => false,
							'validation_method' => "Storage::File::validStatus()"
						)
					)
				),
				'getFilePrivileges' => array(
					'description'	=> "Get privileges for a file",
					'authentication_required'	=> false,
					'parameters'	=> array(
						'code'			=> array(
							'description'		=> "Unique code for repository",
							'validation_method'	=> "Storage::File::validCode()",
							'requirement_group' => 0
						),
						'id'			=> array(
							'content_type'		=> "integer",
							'description'		=> "Unique id for repository",
							'requirement_group' => 1
						),
						'repository_code'	=> array(
							'requirement_group' => 2,
							'description'	=> "Unique code for repository",
							'validation_method' => "Storage::Repository::validCode()"
						),
						'path'			=> array(
							'description'	=> "Path to file",
							'requirement_group' => 2,
							'validation_method' => "Storage::File::validPath()"
						),
						'name'			=> array(
							'description'	=> "Name of file",
							'requirement_group' => 2,
							'validation_method' => "Storage::File::validName()"
						)
					)
				),
				'readPermitted' => array(
					'description'	=> "Check if user is permitted to read a file",
					'authentication_required'	=> false,
					'parameters'	=> array(
						'user_code'	=> array(
							'description'	=> "Unique code for user",
							'validation_method'	=> 'Register::Customer::validCode()'
						),
						'file_code'	=> array(
							'description'	=> "Unique code for file",
							'validation_method'	=> 'Storage::File::validCode()',
							'required' => true
						)
					)
				),
				'writePermitted' => array(
					'description'	=> "Check if user is permitted to write to a file",
					'authentication_required'	=> false,
					'parameters'	=> array(
						'user_code'	=> array(
							'description'	=> "Unique code for user",
							'validation_method'	=> 'Register::Customer::validCode()'
						),
						'file_code'	=> array(
							'description'	=> "Unique code for file",
							'validation_method'	=> 'Storage::File::validCode()',
							'required' => true
						)
					)
				),
				'updateFilePrivileges' => array(
					'description'	=> "Update privileges for a file",
					'authentication_required'	=> true,
					'token_required'			=> true,
					'parameters'		=> array(
						'file_code'			=> array(
							'description'		=> "Unique code for file",
							'validation_method'	=> 'Storage::File::validCode()',
							'required' => true
						),
						'entity_type'		=> array(
							'required' => true,
							'options' => array('organization','user','role','all')
						),
						'entity_id'		=> array(
							'content_type'	=> "integer",
							'description'	=> "Unique id for entity"
						),
						'mask'			=> array(
							'required' => true
						)
					)
				),
				'deleteFile' => array(
					'description'	=> "Delete a file",
					'authentication_required'	=> true,
					'token_required'			=> true,
					'parameters'	=> array(
						'code'		=> array(
							'description'	=> "Unique code for file",
							'validation_method'	=> 'Storage::File::validCode()',
							'required' => true
						)
					)
				),
				'findFiles'	=> array(
					'description'	=> 'Find matching files',
					'parameters'	=> array(
						'code'		=> array(
							'description'	=> "Unique code for file",
							'validation_method'	=> 'Storage::File::validCode()'
						),
						'name'		=> array(
							'description'	=> "Name of file",
							'validation_method'	=> 'Storage::File::validName()'
						),
						'status'	=> array(
							'description'	=> "Status of file",
							'options'		=> array(
								'[NULL]'	=> 'any',
								'NEW'		=> "NEW",
								'ACTIVE'	=> "ACTIVE",
								'INACTIVE'	=> "INACTIVE"
							)
						),
						'repository_code'	=> array(
							'description'	=> "Unique code for repository",
							'validation_method'	=> 'Storage::Repository::validCode()'
						)
					)
				),
				'setFileMetadata' => array(
					'description'	=> "Set metadata for a file",
					'authentication_required'	=> true,
					'token_required'			=> true,
					'parameters'		=> array(
						'code'		=> array(
							'description'	=> "Unique code for file",
							'required' => true,
							'validation_method'	=> 'Storage::File::validCode()'
						),
						'key'		=> array(
							'description'	=> "Key for metadata",
							'required' => true,
							'validation'	=> "Storage::File::validMetadataKey()"
						),
						'value'		=> array(
							'description'	=> "Value for metadata",
							'required' => true,
							'validation'	=> "Storage::File::safeString()"
						)
					)
				),
				'getFileMetadata' => array(
					'description'	=> "Get metadata for a file",
					'authentication_required'	=> true,
					'parameters'		=> array(
						'code'		=> array(
							'description'	=> "Unique code for file",
							'required' => true,
							'validation_method'	=> 'Storage::File::validCode()'
						),
						'key'		=> array(
							'description'	=> "Key for metadata",
							'required' => true,
							'validation'	=> "Storage::File::validMetadataKey()"
						)
					)
				),
				'downloadFile' => array(
					'description'	=> "Download a file",
					'parameters'	=> array(
						'code'		=> array(
							'description'	=> "Unique code for file",
							'required' => true,
							'validation_method'	=> 'Storage::File::validCode()'
						)
					)
				)
			);
		}
	}
