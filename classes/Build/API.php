<?php
	namespace Build;

	class API Extends \API {

		public function __construct() {
			$this->_admin_role = 'build manager';
			$this->_name = 'build';
			$this->_version = '0.2.1';
			$this->_release = '2020-01-15';
			$this->_schema = new \Build\Schema();
			parent::__construct();
		}
		
		###################################################
		### Add a Product								###
		###################################################
		public function addProduct() {
			$product = new \Build\Product();

			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['architecture'])) $parameters['architecture'] = $_REQUEST['architecture'];
			if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
			if (isset($_REQUEST['workspace'])) $parameters['workspace'] = $_REQUEST['workspace'];
			if (isset($_REQUEST['major_version'])) $parameters['major_version'] = $_REQUEST['major_version'];
			if (isset($_REQUEST['minor_version'])) $parameters['minor_version'] = $_REQUEST['minor_version'];
			if (! $product->add($parameters)) app_error("Error adding product: ".$product->error());

			$response = new \HTTP\Response();
			$response->success = 1;
			$response->product = $product;

			print $this->formatOutput($response);
		}

		###################################################
		### Update a Product							###
		###################################################
		public function updateProduct() {
			$product = new \Build\Product();
			$product->get($_REQUEST['code']);
			if ($product->error) app_error("Error finding product: ".$product->error(),'error',__FILE__,__LINE__);
			if (! $product->id) error("Request not found");
	
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['description'])) $parameters['description'] = $_REQUEST['description'];
			if (isset($_REQUEST['architecture'])) $parameters['architecture'] = $_REQUEST['architecture'];
			if (isset($_REQUEST['workspace'])) $parameters['workspace'] = $_REQUEST['workspace'];
			if (isset($_REQUEST['major_version'])) $parameters['major_version'] = $_REQUEST['major_version'];
			if (isset($_REQUEST['minor_version'])) $parameters['minor_version'] = $_REQUEST['minor_version'];
	
			$product->update($parameters);
			if ($product->error()) app_error("Error updating product: ".$product->error(),'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->product = $product;
	
			print $this->formatOutput($response);
		}

		###################################################
		### Get Specified Product						###
		###################################################
		public function getProduct() {
			$product = new \Build\Product();
			$product->get($_REQUEST['name']);
			if ($product->error()) app_error($product->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->product = $product;
	
			print $this->formatOutput($response);
		}

		###################################################
		### Find matching Products						###
		###################################################
		public function findProducts() {
			$productList = new \Build\ProductList();
			
			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			
			$countries = $productList->find($parameters);
			if ($productList->error) app_error("Error finding countries: ".$productList->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->product = $countries;
	
			print $this->formatOutput($response);
		}
		###################################################
		### Add a Version								###
		###################################################
		public function addVersion() {
			if ($_REQUEST['product_id']) {
				$product = new \Build\Product($_REQUEST['product_id']);
			}
			elseif ($_REQUEST['product']) {
				$product = new \Build\Product();
				$product->get($_REQUEST['product']);
			}
			else {
				app_error("product_id or product required");
			}
			if (! $product->id) app_error("Product not found");
			if ($_REQUEST['user_id']) {
				$user = new \Register\Customer($_REQUEST['user_id']);
			}
			elseif ($_REQUEST['user']) {
				$user = new \Register\Customer();
				$user->get($_REQUEST['user']);
			}
			if (! $user->id) app_error("User not found");
	
			$version = new \Build\Version();
	
			$parameters = array();
			if (isset($_REQUEST['number'])) $parameters['number'] = $_REQUEST['number'];
			$parameters['major_number'] = $_REQUEST['major_number'];
			$parameters['minor_number'] = $_REQUEST['minor_number'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
			if (isset($_REQUEST['tarball'])) $parameters['tarball'] = $_REQUEST['tarball'];
			if (isset($_REQUEST['message'])) $parameters['message'] = $_REQUEST['message'];
			$parameters['product_id'] = $product->id;
			if (! $version->add($parameters)) app_error("Error adding version: ".$version->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->version = $version;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Version							###
		###################################################
		public function updateVersion() {
			$version = new \Build\Version($_REQUEST['version_id']);
	
			$parameters = array();
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
			if (isset($_REQUEST['tarball'])) $parameters['tarball'] = $_REQUEST['tarball'];
			if (isset($_REQUEST['message'])) $parameters['message'] = $_REQUEST['message'];
			$version->update(
				$parameters
			);
			if ($version->error) app_error("Error updating version: ".$version->error(),'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->version = $version;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Specified Version						###
		###################################################
		public function getVersion() {
			$product = new \Build\Product($_REQUEST['product_id']);
			if (! $product->id) {
				$this->_error = "Product Not Found";
				return false;
			}
	
			$version = new \Build\Version();
			if (! $version->get($product->id,$_REQUEST['number'])) app_error("Version not found");
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->version = $version;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Versions						###
		###################################################
		public function findVersions() {
			$versionList = new \Build\VersionList();
			
			$parameters = array();
			if ($_REQUEST['status']) $parameters['status'] = $_REQUEST['status'];
			if ($_REQUEST['product']) {
				$product = new \Build\Product();
				if (! $product->get($_REQUEST['product'])) app_error("Product not found");
				$parameters['product_id'] = $product->id;
			}
			elseif ($_REQUEST['product_id']) {
				$product = new \Build\Product($_REQUEST['product_id']);
				$parameters['product_id'] = $product->id;
			}
			if (isset($_REQUEST['user'])) {
				$user = new \Register\Customer();
				if (! $user->get($_REQUEST['user'])) app_error("User not found");
				$parameters['user_id'] = $user->id;
			}
			if (isset($_REQUEST['number'])) $parameters['number'] = $_REQUEST['number'];
			if (isset($_REQUEST['status'])) $parameters['status'] = $_REQUEST['status'];
			
			$versions = $versionList->find($parameters);
			if ($versionList->error) app_error("Error finding versions: ".$versionList->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->version = $versions;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Add a Repository							###
		###################################################
		public function addRepository() {
			if (! isset($_REQUEST['url'])) app_error("url required");
	
			$repository = new \Build\Repository();
	
			$parameters = array();
			if (isset($_REQUEST['url'])) $parameters['url'] = $_REQUEST['url'];
			if (! $repository->add($parameters)) app_error("Error adding repository: ".$repository->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->repository = $repository;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Repository							###
		###################################################
		public function updateRepository() {
			$repository = new \Build\Repository();
			$repository->get($_REQUEST['url']);
			if ($repository->error) app_error("Error finding repository: ".$repository->error(),'error',__FILE__,__LINE__);
			if (! $repository->id) error("Request not found");
	
			$parameters = array();
			if (isset($_REQUEST['url'])) $parameters['url'] = $_REQUEST['url'];
			$repository->update(
				$parameters
			);
			if ($repository->error) app_error("Error updating repository: ".$repository->error(),'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->repository = $repository;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Specified Repository					###
		###################################################
		public function getRepository() {
			$repository = new \Build\Repository();
			if (! $repository->get($_REQUEST['url'])) app_error("Product not found");
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->repository = $repository;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Repositories					###
		###################################################
		public function findRepositories() {
			$repositoryList = new \Build\RepositoryList();
	
			$parameters = array();
			$repositories = $repositoryList->find($parameters);
			if ($repositoryList->error) app_error("Error finding repositories: ".$repositoryList->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->repository = $repositories;
	
			print $this->formatOutput($response);
		}
		
		###################################################
		### Add a Commit								###
		###################################################
		public function addCommit() {
			if (! isset($_REQUEST['hash'])) app_error("hash required");
	
			if (isset($_REQUEST['repository_id'])) {
				$repository = new \Build\Repository($_REQUEST['repository_id']);
				if (! $repository->id) app_error("Repository not found");
			}
			elseif(isset($_REQUEST['url'])) {
				$repository = new \Build\Repository();
				if (! $repository->get($_REQUEST['url'])) app_error("Repository not found");
			}
	
			$parameters = array();
			$parameters['repository_id'] = $repository->id;
			$parameters['hash'] = $repository->hash;
			if (isset($parameters['timestamp'])) {
				if (get_mysql_time($_REQUEST['timestamp'])) $parameters['timestamp'] = get_mysql_time($_REQUEST['timestamp']);
				else app_error("Invalid timestamp");
			}
			if (isset($_REQUEST['author'])) {
				$author = new \Register\Customer();
				if (! $author->get($_REQUEST['author'])) app_error("Author not found");
				$parameters['author_id'] = $author->id;
			}
			if (! $commit->add($parameters)) app_error("Error adding commit: ".$commit->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->commit = $commit;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Update a Commit								###
		###################################################
		public function updateCommit() {
			if (isset($_REQUEST['url'])) {
				$repository = new \Build\Repository();
				if (! $repository->get($_REQUEST['url'])) app_error("Repository not found");
			}
			elseif (isset($_REQUEST['repository_id'])) {
				$repository = new \Build\Repository($_REQUEST['repository_id']);
				if (! $repository->id) app_error("Repository not found");
			}
	
			$commit = new \Build\Commit();
			$commit->get($repository->id,$_REQUEST['hash']);
			if ($commit->error) app_error("Error finding commit: ".$commit->error(),'error',__FILE__,__LINE__);
			if (! $commit->id) error("Commit not found");
	
			$parameters = array();
			if (isset($_REQUEST['author_id'])) $parameters['author_id'] = $_REQUEST['author_id'];
			$commit->update(
				$parameters
			);
			if ($commit->error) app_error("Error updating commit: ".$commit->error(),'error',__FILE__,__LINE__);
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->commit = $commit;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Specified Commit						###
		###################################################
		public function getCommit() {
			if (isset($_REQUEST['url'])) {
				$repository = new \Build\Repository();
				if (! $repository->get($_REQUEST['url'])) app_error("Repository not found");
			}
			elseif (isset($_REQUEST['repository_id'])) {
				$repository = new \Build\Repository($_REQUEST['repository_id']);
				if (! $repository->id) app_error("Repository not found");
			}
	
			$commit = new \Build\Commit();
			$commit->get($repository->id,$_REQUEST['hash']);
			if ($commit->error) app_error("Error finding commit: ".$commit->error(),'error',__FILE__,__LINE__);
			if (! $commit->id) error("Commit not found");
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->commit = $commit;
	
			print $this->formatOutput($response);
		}
	
		###################################################
		### Find matching Commits						###
		###################################################
		public function findCommits() {
			$commitList = new \Build\CommitList();
			if (isset($_REQUEST['url'])) {
				$repository = new \Build\Repository();
				if (! $repository->get($_REQUEST['url'])) app_error("Repository not found");
			}
			elseif (isset($_REQUEST['repository_id'])) {
				$repository = new \Build\Repository($_REQUEST['repository_id']);
				if (! $repository->id) app_error("Repository not found");
			}
			if (isset($_REQUEST['author'])) {
				$author = new \Register\Customer();
				if ($author->get($_REQUEST['author'])) $parameters['author_id'] = $author->id;
				else app_error("Author not found");
			}
			elseif(isset($_REQUEST['author_id'])) {
				$author = new \Register\Customer($_REQUEST['author_id']);
				if (! $author->id) {
					$this->_error = "Author not found";
					return false;
				}
			}
	
			$parameters = array();
			if ($repository->id) $parameters['repository_id'] = $repository->id;
			if ($author->id) $parameters['author_id'] = $author->id;
	
			$commits = $commitList->find($parameters);
			if ($commitList->error) app_error("Error finding commits: ".$commitList->error());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->commit = $commits;
	
			print $this->formatOutput($response);
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'addProduct'	=> array(
					'name'		=> array('required' => true),
					'workspace'	=> array(),
					'major_version'	=> array('required' => true, 'default' => 0),
					'minor_version'	=> array('required' => true, 'default' => 0),
				),
				'updateProduct'	=> array(
					'name'		=> array('required' => true),
					'workspace'	=> array(),
					'major_version'	=> array(),
					'minor_version'	=> array(),
				),
				'findProducts'	=> array(),
				'getProduct'	=> array(
					'name'		=> array('required' => true),
				),
				'addVersion'	=> array(
					'product_id'	=> array('required' => true),
					'major_number'	=> array('required' => true),
					'minor_number'	=> array('required' => true),
					'user'			=> array('required' => true),
					'number'		=> array('required' => true),
					'status'		=> array('required' => true),
					'tarball'		=> array('required' => true),
					'message'		=> array('required' => true),
				),
				'findVersions'	=> array(
					'product'	=> array(),
					'status'	=> array(),
				),
				'addRepository'	=> array(
					'name'		=> array(),
					'url'		=> array(),
				),
				'updateRepository'	=> array(
					'name'			=> array('required' => true),
					'url'			=> array()
				),
				'findRepositories'	=> array(),
				'getRepository'		=> array(
					'url'			=> array('required'),
				),
			);
		}
	}
?>