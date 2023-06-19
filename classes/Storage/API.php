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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage storage repositories')) error('storage manager role required');
			$repository = new \Storage\Repository();
			if ($repository->error()) $this->error("Error adding repository: ".$repository->error());
			$repository->get($_REQUEST['code']);
			if ($repository->error()) $this->app_error("Error finding repository: ".$repository->error(),__FILE__,__LINE__);
			if (! $repository->id) $this->error("Repository not found");

			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

			$repository->update($parameters);
			if ($repository->error()) $this->app_error("Error updating repository: ".$repository->error(),__FILE__,__LINE__);

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

			$metadata = new \Storage\Repository\Metadata($repository->id,$_REQUEST['key']);
			if ($metadata->error()) $this->app_error("Error getting metadata: ".$metadata->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->addElement('metadata',$metadata);
			$response->print();
		}

		###################################################
		### Add a File									###
		###################################################
		public function addFile() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('upload storage files')) error('storage upload role required');

			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->get($_REQUEST['repository_code']);
			if ($factory->error()) $this->error("Error loading repository: ".$factory->error());
			if (! $repository->id) $this->error("Repository not found");
			app_log("Identified repo '".$repository->name."'");

			if (! $_REQUEST['name']) $_REQUEST['name'] = $_FILES['file']['name'];
			if (! file_exists($_FILES['file']['tmp_name'])) $this->error("Temp file '".$_FILES['file']['tmp_name']."' not found");
			if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = $_FILES['file']['type'];
			if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = mime_content_type($_FILES['file']['name']);
			if (! $_REQUEST['mime_type']) $_REQUEST['mime_type'] = guess_mime_type($_FILES['file']['name']);
			if (! $_REQUEST['mime_type']) $this->error("mime_type not available for '".$_FILES['file']['name']."'");

			app_log("Storing ".$_REQUEST['name']." to ".$repository->path);
			if (isset($_REQUEST['read_protect']) && strlen($_REQUEST['read_protect']) && $_REQUEST['read_protect'] != 'NONE') {
				if ($repository->endpoint) {
					$this->error("Can't protect a file in a repository with external endpoint");
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

			# Upload File Into Repository
			if ($file->error()) $this->error("Error adding file: ".$file->error());
			if (! $repository->addFile($file,$_FILES['file']['tmp_name'])) {
				$file->delete();
				$this->error('Unable to add file to repository: '.$repository->error());
			}
			app_log("Stored file ".$file->id." at ".$repository->path."/".$file->code);

			$response = new \APIResponse();
			$response->addElement('file',$file);
			$response->print();
		}

		###################################################
		### Update a File								###
		###################################################
		public function updateFile() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('upload storage files')) error('storage upload role required');
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");

			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('manage storage files')) error('storage upload role required');
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->notFound("File not found");

			# Remove File From Repository
			$repository = $file->repository();
			if (! $file->repository()->eraseFile($file)) $this->app_error("Failed to delete file ".$_REQUEST['code'].": ".$repository->error());

			$file->delete();
			if ($file->error()) $this->app_error("Error deleting file: ".$file->error(),__FILE__,__LINE__);

			$response = new \APIResponse();
			$response->print();
		}

		###################################################
		### Is User Permitted to Read File			###
		###################################################
		public function readPermitted() {
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['file_code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->notFound("File not found");

			$user_id = null;
			if (! empty($_REQUEST['user_code'])) {
				$user = new \Register\Customer();
				if ($user->get($_REQUEST['user_code'])) {
					$user_id = $user->id;
				}
				else {
					$this->error("User not found");
				}
			}

			$response = new \APIResponse();
			if ($file->readPermitted($user_id)) $response->addElement('permitted',1);
			else $response->addElement('permitted',0);
			$response->print();
		}

		###################################################
		### Is User Permitted to Update File			###
		###################################################
		public function writePermitted() {
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['file_code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");

			$user_id = null;
			if (! empty($_REQUEST['user_code'])) {
				$user = new \Register\Customer();
				if ($user->get($_REQUEST['user_code'])) {
					$user_id = $user->id;
				}
				else {
					$this->notFound("User not found");
				}
			}

			$response = new \APIResponse();
			if ($file->writePermitted($user_id)) $response->addElement('permitted',1);
			else $response->addElement('permitted',0);
			$response->print();
		}

		###################################################
		### Get Privileges for a File					###
		###################################################
		public function getFilePrivileges() {
			$file = new \Storage\File();
			if ($file->error()) $this->error("Error initializing file: ".$file->error());
			$file->get($_REQUEST['code']);
			if ($file->error()) $this->app_error("Error finding file: ".$file->error(),__FILE__,__LINE__);
			if (! $file->id) $this->notFound("File not found");
			if ($file->user()->id != $GLOBALS['_SESSION_']->customer->id && ! $GLOBALS['_SESSION_']->customer->can('update storage file permissions')) error('permission denied');

			$privileges = $file->getPrivileges();
			$document = array();
			foreach ($privileges as $object => $privilege) {
				if ($object == "u") {
					foreach ($privilege as $id => $perms) {
						//print "User => $id: ";
						$user = new \Register\Person($id);
						$document["user"][$user->code]['can'] = $perms;
					}
				}
				if ($object == "o") {
					foreach ($privilege as $id => $perms) {
						//print "User => $id: ";
						$organization = new \Register\Organization($id);
						$document["organization"][$organization->code]['can'] = $perms;
					}
				}
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
			if ($metadata->error()) $this->app_error("Error getting metadata: ".$metadata->error(),__FILE__,__LINE__);

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
			return array(
				'ping'			=> array(),
				'addRepository'	=> array(
					'code'		=> array(),
					'type'		=> array('required' => true),
					'name'		=> array('required' => true),
					'status'	=> array(),
					'path'		=> array('required' => true),
				),
				'updateRepository'	=> array(
					'code'		=> array(),
					'name'		=> array('required' => true),
					'status'	=> array(),
				),
				'findRepositories'	=> array(
					'code'		=> array(),
					'name'		=> array(),
					'status'	=> array(),
					'type'		=> array(),
				),
				'setRepositoryMetadata' => array(
					'code'		=> array('required' => true),
					'key'		=> array('required' => true),
					'value'		=> array('required' => true)
				),
				'getRepositoryMetadata' => array(
					'code'		=> array('required' => true),
					'key'		=> array('required' => true)
				),
				'addFile' => array(
					'repository_code'	=> array('required' => true),
					'name'		=> array(),
					'mime_type'	=> array(),
					'file'		=> array('type' => 'file'),
				),
				'updateFile' => array(
					'code'		=> array('required' => true),
					'name'		=> array(),
					'status'	=> array()
				),
				'getFilePrivileges' => array(
					'code'			=> array('required' => true)
				),
				'readPermitted' => array(
					'user_code'	=> array(),
					'file_code'	=> array('required' => true)
				),
				'writePermitted' => array(
					'user_code'	=> array(),
					'file_code'	=> array('required' => true)
				),
				'updateFilePrivileges' => array(
					'file_code'			=> array('required' => true),
					'entity_type'		=> array('required' => true,'options' => array('organization','user','role','all')),
					'entity_id'		=> array(),
					'mask'			=> array('required' => true)
				),
				'deleteFile' => array(
					'code'		=> array('required' => true),
				),
				'findFiles'	=> array(
					'code'		=> array(),
					'name'		=> array(),
					'status'	=> array(),
					'repository_code'	=> array(),
				),
				'setFileMetadata' => array(
					'code'		=> array('required' => true),
					'key'		=> array('required' => true),
					'value'		=> array('required' => true)
				),
				'getFileMetadata' => array(
					'code'		=> array('required' => true),
					'key'		=> array('required' => true)
				),
				'downloadFile' => array(
					'code'		=> array('required' => true)
				)
			);
		}
	}
