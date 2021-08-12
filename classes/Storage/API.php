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
			if (! $GLOBALS['_SESSION_']->customer->has_role('storage manager')) error('storage manager role required');
			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->create($_REQUEST['type']);
			if ($factory->error) $this->error("Error adding repository: ".$factory->error);
			$repository->add(
				array(
					'code'				=> $_REQUEST['code'],
					'name'				=> $_REQUEST['name'],
					'status'			=> $_REQUEST['status'],
					'path'				=> $_REQUEST['path']
				)
			);
			if ($repository->error) $this->error("Error adding repository: ".$repository->error);

			$this->response->success = 1;
			$this->response->repository = $repository;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Update a Repository							###
		###################################################
		public function updateRepository() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('storage manager')) error('storage manager role required');
			$repository = new \Storage\Repository();
			if ($repository->error) $this->error("Error adding repository: ".$repository->error);
			$repository->get($_REQUEST['code']);
			if ($repository->error) $this->app_error("Error finding repository: ".$repository->error,__FILE__,__LINE__);
			if (! $repository->id) $this->error("Repository not found");

			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['type'])) $parameters['type'] = $_REQUEST['type'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

			$repository->update($parameters);
			if ($repository->error) $this->app_error("Error updating repository: ".$repository->error,__FILE__,__LINE__);

			$this->response->success = 1;
			$this->response->repository = $repository;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}
	
		###################################################
		### Find matching Repository					###
		###################################################
		public function findRepositories() {
			$repositorylist = new \Storage\RepositoryList();
			if ($repositorylist->error) app_error("Error initializing repository list: ".$repositorylist->error,__FILE__,__LINE__);

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
			if ($repositorylist->error) app_error("Error finding repositories: ".$repositorylist->error,__FILE__,__LINE__);

			$this->response->success = 1;
			$this->response->repository = $shownRepositories;

			api_log($shownRepositories);
			print $this->formatOutput($this->response);
		}
		
		###################################################
		### Set Repository Metadata						###
		###################################################
		public function setRepositoryMetadata() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('storage manager')) error('storage manager role required');
			$repositoryList = new \Storage\RepositoryList();
			list($repository) = $repositoryList->find(array("code" => $_REQUEST['code']));
			if ($repositoryList->error) $this->app_error("Error finding repository: ".$repository->error,__FILE__,__LINE__);
			if (! $repository->id) $this->error("Repository not found");

			$repository->setMetadata($_REQUEST['key'],$_REQUEST['value']);
			if ($repository->error) $this->error($repository->error);

			$repository->get($_REQUEST['code']);

			$this->response->success = 1;
			$this->response->repository = $repository;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}
		
		###################################################
		### Get Repository Metadata						###
		###################################################
		public function getRepositoryMetadata() {
			$repository = new \Storage\Repository();
			if ($repository->error) $this->app_error("Error initializing repository: ".$repository->error,__FILE__,__LINE__);

			$repository->get($_REQUEST['code']);
			if ($repository->error) $this->app_error("Error finding repository: ".$repository->error,__FILE__,__LINE__);
			if (! $repository->id) $this->error("Repository '".$_REQUEST['code']."' not found");

			$metadata = new \Storage\Repository\Metadata($repository->id,$_REQUEST['key']);
			if ($metadata->error) $this->app_error("Error getting metadata: ".$metadata->error,__FILE__,__LINE__);

			$this->response->success = 1;
			$this->response->metadata = $metadata;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Add a File									###
		###################################################
		public function addFile() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');

			$factory = new \Storage\RepositoryFactory();
			$repository = $factory->get($_REQUEST['repository_code']);
			if ($factory->error) $this->error("Error loading repository: ".$factory->error);
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
			if ($file->error) $this->error("Error initializing file: ".$file->error);
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
			if ($file->error) $this->error("Error adding file: ".$file->error);
			if (! $repository->addFile($file,$_FILES['file']['tmp_name'])) {
				$file->delete();
				$this->error('Unable to add file to repository: '.$repository->error);
			}
			app_log("Stored file ".$file->id." at ".$repository->path."/".$file->code);

			$this->response->success = 1;
			$this->response->file = $file;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Update a File								###
		###################################################
		public function updateFile() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');
			$file = new \Storage\File();
			if ($file->error) $this->error("Error initializing file: ".$file->error);
			$file->get($_REQUEST['code']);
			if ($file->error) $this->app_error("Error finding file: ".$file->error,__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");

			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];

			$file->update($parameters);
			if ($file->error) $this->app_error("Error updating file: ".$file->error,__FILE__,__LINE__);

			$this->response->success = 1;
			$this->response->file = $file;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Delete a File								###
		###################################################
		public function deleteFile() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');
			$file = new \Storage\File();
			if ($file->error) $this->error("Error initializing file: ".$file->error);
			$file->get($_REQUEST['code']);
			if ($file->error) $this->app_error("Error finding file: ".$file->error,__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");

			# Remove File From Repository
			$repository = $file->repository();
			if (! $file->repository->eraseFile($file)) {
				app_log("Failed to delete file ".$_REQUEST['code'].": ".$repository->error,'error',__FILE__,__LINE__);
			}

			$file->delete();
			if ($file->error) $this->app_error("Error deleting file: ".$file->error,__FILE__,__LINE__);

			$this->response->success = 1;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Find matching file							###
		###################################################
		public function findFiles() {
			$filelist = new \Storage\FileList();
			if ($filelist->error) $this->app_error("Error initializing file list: ".$filelist->error,__FILE__,__LINE__);

			$parameters = array();
			if (isset($_REQUEST['code']) and strlen($_REQUEST['code'])) $parameters['code'] = $_REQUEST['code'];
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
			if (isset($_REQUEST['repository_code']) && strlen($_REQUEST['repository_code'])) {
				$repositorylist = new \Storage\RepositoryList();
				list($repository) = $repositorylist->find(array('code' => $_REQUEST['repository_code']));
				if ($repositorylist->error) $this->error("Error finding repository");
				if (! $repository->id) $this->error("Repository not found");
				$parameters['repository_id'] = $repository->id;
			}
			$files = $filelist->find($parameters);

			if ($filelist->error) $this->app_error("Error finding filelist: ".$filelist->error,__FILE__,__LINE__);

			$this->response->success = 1;
			$this->response->file = $files;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}
		
		###################################################
		### Set File Metadata							###
		###################################################
		public function setFileMetadata() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');
			$file = new \Storage\File();
			if ($file->error) $this->app_error("Error initializing file: ".$file->error,__FILE__,__LINE__);

			$file->get($_REQUEST['code']);
			if ($file->error) $this->app_error("Error finding file: ".$file->error,__FILE__,__LINE__);
			if (! $file->id) error("File '".$_REQUEST['code']."' not found");

			$file->setMetadata($_REQUEST['key'],$_REQUEST['value']);
			if ($file->error) $this->app_error("Error setting metadata: ".$file->error,__FILE__,__LINE__);

			$file->get($_REQUEST['code']);
			$this->response->success = 1;
			$this->response->file = $file;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}
		
		###################################################
		### Get File Metadata						    ###
		###################################################
		public function getFileMetadata() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('storage upload')) error('storage upload role required');
			$file = new \Storage\File();
			if ($file->error) $this->app_error("Error initializing file: ".$file->error,__FILE__,__LINE__);

			$file->get($_REQUEST['code']);
			if ($file->error) $this->app_error("Error finding file: ".$file->error,__FILE__,__LINE__);
			if (! $file->id) $this->error("File '".$_REQUEST['code']."' not found");

			$metadata = $file->getMetadata($_REQUEST['key']);
			if ($metadata->error) $this->app_error("Error getting metadata: ".$metadata->error,__FILE__,__LINE__);

			$this->response->success = 1;
			$this->response->metadata = $metadata;

			api_log($this->response);
			print $this->formatOutput($this->response);
		}

		###################################################
		### Download matching file						###
		###################################################
		public function downloadFile() {
			$file = new \Storage\File();
			$file->get($_REQUEST['code']);
			if ($file->error) $this->app_error("Error getting file: ".$file->error,__FILE__,__LINE__);
			if (! $file->id) $this->error("File not found");
			$file->repository->retrieveFile($file);
			if ($file->error) $this->app_error("Error getting file: ".$file->error,__FILE__,__LINE__);
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
			$this->response->success = 1;
			$this->response->count = $count;

			api_log($this->response);
			print $this->formatOutput($this->response);
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
