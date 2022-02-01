<?php
	namespace Site;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'site';
			$this->_version = '0.1.1';
			$this->_release = '2020-01-17';
			$this->_schema = new \Site\Schema();
			parent::__construct();
		}
		
		###################################################
		### Query Page List								###
		###################################################
		public function findPages() {
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'content.message.xsl';

			# Initiate Page List
			$page_list = new \Site\PageList();

			# Find Matching Threads
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['module'])) $parameters['module'] = $_REQUEST['module'];
			if (isset($_REQUEST['options'])) $parameters['options'] = $_REQUEST['options'];
			$pages = $page_list->find($parameters);

			# Error Handling
			if ($page_list->error) error($page_list->error);
			else{
				$this->response->page = $pages;
				$this->response->success = 1;
			}

			api_log('content',$_REQUEST,$this->response);

			# Send Response
			print $this->formatOutput($this->response);
		}
		###################################################
		### Get Details regarding Specified Page		###
		###################################################
		public function getPage() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Page Object
			$page = new \Site\Page();
			if (isset($_REQUEST['module'])) $page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index']);
			elseif (isset($_REQUEST['target'])) $page->get('content','index',$_REQUEST['target']);
	
			# Error Handling
			if ($page->error) error($page->error);
			elseif ($page->id) {
				$response->request = $_REQUEST;
				$response->page = $page;
				$response->success = 1;
			}
			else {
				$response->success = 0;
				$response->error = "Page not found";
			}
	
			api_log('content',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		public function addPage() {
			if (! $GLOBALS['_SESSION_']->customer->can('edit site pages')) error("Permission Denied");
	
			if (! $_REQUEST['module']) error("Module required");
			if (! $_REQUEST['view']) error("View required");
			if (! $_REQUEST['index']) $_REQUEST['index'] = '';
			if (! preg_match('/^[\w\-\.\_]+$/',$_REQUEST['module'])) error("Invalid module name");
			if (! preg_match('/^[\w\-\.\_]+$/',$_REQUEST['view'])) error("Invalid view name");
	
			$page = new \Site\Page();
			if ($page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) error("Page already exists");
			$page->add($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index']);
			if ($page->error) error("Error adding page: ".$page->error);
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->page = $page;
			print $this->formatOutput($response);
		}
		###################################################
		### Get Details regarding Specified Product		###
		###################################################
		public function addMessage() {
			if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) error("Permission Denied");
	
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Product Object
			$_content = new \Content\Message();
	
			# Find Matching Threads
			$message = $_content->add(
				array (
					'name'			=> $_REQUEST['name'],
					'target'		=> $_REQUEST['target'],
					'title'			=> $_REQUEST['title'],
					'content'		=> $_REQUEST['content']
				)
			);
	
			# Error Handling
			if ($_content->error) error($_content->error);
			else{
				$response->message = $message;
				$response->success = 1;
			}
	
			api_log('content',$_REQUEST,$response);
	
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Update Specified Message					###
		###################################################
		public function updateMessage() {
			if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) error("Permission Denied");
	
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Product Object
			$content = new \Content\Message($_REQUEST['id']);
			if (! $content->id) error("Message '".$_REQUEST['id']."' not found");
	
			# Find Matching Threads
			$content->update(
				array (
					'name'			=> $_REQUEST['name'],
					'title'			=> $_REQUEST['title'],
					'content'		=> $_REQUEST['content']
				)
			);
	
			# Error Handling
			if ($content->error) error($content->error);
			else{
				$response->content = $content;
				$response->success = 1;
			}
	
			api_log('content',$_REQUEST,$response);
			# Send Response
			print $this->formatOutput($response);
		}
		###################################################
		### Purge Cache of Specified Message			###
		###################################################
		public function purgeMessage() {
			if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) error("Permission Denied");
	
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Product Object
			$message = new \Content\Message();
	
			# Get Message
			if ($message->get($_REQUEST['target'])) {
				# Purge Cache for message
				$message->purge_cache();
		
				# Error Handling
				if ($message->error) error($message->error);
				else{
					$response->message = "Success";
					$response->success = 1;
				}
			}
			else {
				$this->app_error($message->error,__FILE__,__LINE__);
			}
	
			api_log('content',$_REQUEST,$response);
			# Send Response
			print $this->formatOutput($response);
		}
	
		###################################################
		### Get Metadata for current view				###
		###################################################
		public function getPageMetadata() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();
	
			$page = new \Site\Page();
			if ($page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) {
				if ($metadata = $page->getMetadata($_REQUEST['key'])) {
					$response->metadata = $metadata;
					$response->success = 1;
				}
				else {
					$this->app_error("Cannot get metadata: ".$page->error);
				}
			}
			else {
				app_error("Page not found");
			}
	
			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response);
		}

		###################################################
		### Get Metadata for current view				###
		###################################################
		public function findPageMetadata() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();
	
			$page = new \Site\Page();
			if ($page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) {
				# Initiate Metadata Object
				$metadata = $page->allMetadata();

				$response->metadata = $metadata;
				$response->success = 1;
			}
			else {
				$this->app_error("Page not found");
			}
	
			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response);
		}

		public function setPageMetadata() {
			if (! $GLOBALS['_SESSION_']->customer->can('edit site pages')) $this->error("Permission Denied");
	
			$response = new \HTTP\Response();
	
			$page = new \Site\Page();
			if ($page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) {
				if ($page->setMetadata($_REQUEST['key'],$_REQUEST['value'])) {
					$response->success = 1;
					$response->metadata = array('key' => $_REQUEST['key'],'value' => $_REQUEST['value']);
				}
				else {
					$response->success = 0;
					$response->error = "Error setting metadata: ".$page->errorString();
				}
			}
			elseif ($page->errorCount()) {
				$response->success = 0;
				$response->error = "Error finding page '".$_REQUEST['module'].":".$_REQUEST['view']."': ".$page->errorString();
			}
			else {
				$response->success = 0;
				$response->error = "Page '".$_REQUEST['module'].":".$_REQUEST['view']."' Not Found";
			}
			print $this->formatOutput($response);
		}
		###################################################
		### Get List of Site Navigation Menus			###
		###################################################
		public function findNavigationMenus() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.navigationitems.xsl';
			$response = new \HTTP\Response();
	
			# Initiate Product Object
			$menulist = new \Navigation\MenuList();
	
			# Find Matching Threads
			$menus = $menulist->find(
				array (
					'id'			=> $_REQUEST['id'],
					'parent_id'		=> $_REQUEST['parent_id'],
				)
			);
	
			# Error Handling
			if ($menulist->error) error($menulist->error);
			else{
				$response->menu = $menus;
				$response->success = 1;
			}
	
			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response);
		}
		###################################################
		### Get Items from a Site Navigation Menu		###
		###################################################
		public function findNavigationItems() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.navigationitems.xsl';
			$response = new \HTTP\Response();
	
			# Get Menu
			$menu = new \Navigation\Menu();
			if (! $menu->get($_REQUEST['code'])) error("Menu not found");
	
			# Find Matching Threads
			$items = $menu->items();
	
			# Error Handling
			if ($menu->error) error($menu->error);
			else{
				$response->item = $items;
				$response->success = 1;
			}
	
			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response);
		}
		public function deleteConfiguration() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) error("Permission denied");
			$response = new \HTTP\Response();
			$configuration = new \Site\Configuration($_REQUEST['key']);
			if ($configuration->delete()) {
				$response->success = 1;
			}
			else {
				$response->success = 0;
				$response->error = $configuration->error();
			}
			print $this->formatOutput($response);
		}
		public function setConfiguration() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) error("Permission denied");
			$response = new \HTTP\Response();
			$configuration = new \Site\Configuration($_REQUEST['key']);
			if ($configuration->set($_REQUEST['value'])) {
				$response->success = 1;
				$response->configuration = $configuration;
			}
			else {
				$response->success = 0;
				$response->error = $configuration->error();
			}
			print $this->formatOutput($response);
		}
		public function getConfiguration() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('administrator')) error("Permission denied");
			$response = new \HTTP\Response();
			$configuration = new \Site\Configuration($_REQUEST['key']);
			if ($configuration->get($_REQUEST['key'])) {
				$response->success = 1;
				$response->key = $configuration->key();
				$response->value = $configuration->value();
			}
			else {
				$response->success = 0;
				$response->error = $configuration->error();
			}
			print $this->formatOutput($response);
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'findPages'	=> array(
					'name'		=> array(),
					'module'	=> array(),
					'options'	=> array(),
				),
				'getPage'	=> array(
					'module'	=> array(),
					'view'		=> array(),
					'index'		=> array(),
					'target'	=> array(),
				),
				'addPage'	=> array(
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array(),
				),
				'addMessage'	=> array(
					'name'		=> array('required' => true),
					'title'		=> array(),
					'content'	=> array(),
				),
				'updateMessage'	=> array(
					'id'		=> array('required' => true),
					'name'		=> array(),
					'title'		=> array(),
					'content'	=> array(),
				),
				'purgeMessage'	=> array(
					'target'	=> array(),
				),
				'getPageMetadata'	=> array(
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array(),
				),
				'findPageMetadata'	=> array(
					'id'	=> array('required' => true),
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array(),
				),
				'setPageMetadata'	=> array(
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array(),
					'key'		=> array(),
					'value'		=> array(),
				),
				'findNavigationMenus'	=> array(
					'id'		=> array(),
					'parent_id'	=> array()
				),
				'setConfiguration'	=> array(
					'key'		=> array('required' => true),
					'value'		=> array('required' => true),
				),
				'getConfiguration'	=> array(
					'key'		=> array('required' => true),
				),
				'deleteConfiguration'	=> array(
					'key'		=> array('required' => true),
				),
			);
		}
	}
