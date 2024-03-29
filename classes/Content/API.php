<?php
	namespace Content;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_admin_role = 'content operator';
			$this->_name = 'content';
			$this->_version = '0.1.1';
			$this->_release = '2021-07-20';
			$this->_schema = new \Content\Schema();
			parent::__construct();
		}

		###################################################
		### Get A Filtered List of Messages				###
		###################################################
		public function findMessages() {
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$message_list = new \Content\MessageList();

			# Find Matching Threads
			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['options'])) $parameters['options'] = $_REQUEST['options'];
			$messages = $message_list->find($parameters);

			# Error Handling
			if ($message_list->error) $this->error($message_list->error);
			else{
				$response->message = $messages;
				$response->success = 1;
			}

			api_log('content',$_REQUEST,$response);

			# Send Response
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}
		
		###################################################
		### Search for Messages         s				###
		###################################################
		public function searchMessages() {
		
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$message_list = new \Content\MessageList();

			# Find Matching Threads
			$parameters = array();
			
			if (isset($_REQUEST['string'])) $parameters['string'] = $_REQUEST['string'];
			$messages = $message_list->search($parameters);

			# Error Handling
			if ($message_list->error) $this->error($message_list->error);
			else {
				$response->message = $messages;
				$response->success = 1;
			}

			api_log('content',$_REQUEST,$response);

			# Send Response
			print $this->formatOutput($response);
		}
		
		###################################################
		### Get Details regarding Specified Message		###
		###################################################
		public function getMessage() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';

			# Initiate Product Object
			$message = new \Content\Message($_REQUEST['id']);
			if (! isset($_REQUEST['id'])) {
				if (! $_REQUEST['target']) $_REQUEST['target'] = '';

				# Find Matching Threads
				$message->get($_REQUEST['target']);
			}

			# Error Handling
			if ($message->error) $this->error($message->error);
			else{
				$this->response->request = $_REQUEST;
				$this->response->message = $message;
				$this->response->success = 1;
			}

			api_log('content',$_REQUEST,$this->response);

			# Send Response
			print $this->formatOutput($this->response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}
		###################################################
		### Get Details regarding Specified Product		###
		###################################################
		public function addMessage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$content = new \Content\Message();

			# Find Matching Threads
			$message = $content->add(
				array (
					'name'			=> $_REQUEST['name'],
					'target'		=> $_REQUEST['target'],
					'title'			=> $_REQUEST['title'],
					'content'		=> $_REQUEST['content']
				)
			);

			# Error Handling
			if ($content->error) $this->error($content->error);
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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$message = new \Content\Message();
			if (isset($_REQUEST['id']) && $_REQUEST['id']) {
				$message->id = $_REQUEST['id'];
				if (! $message->details()) $this->error("Message id ".$_REQUEST['id']." not found");
			}
			elseif (isset($_REQUEST['target']) && $_REQUEST['target']) {
				if (! $message->get($_REQUEST['target'])) $this->error("Message '".$_REQUEST['target']."' not found");
			}
			else $this->error("Must provide message id or target");
			if (! $message->id) $this->error("Message '".$_REQUEST['id']."' not found");

			$parameters = array();
			if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
			if (isset($_REQUEST['title'])) $parameters['title'] = $_REQUEST['title'];
			if (isset($_REQUEST['content'])) $parameters['content'] = $_REQUEST['content'];

			# Find Matching Threads
			$message->update($parameters);

			# Error Handling
			if ($message->error) $this->error($message->error);
			else{
				$response->message = $message;
				$response->success = 1;
			}

			api_log('content',$_REQUEST,$response);
			# Send Response
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}
		###################################################
		### Purge Cache of Specified Message			###
		###################################################
		public function purgeMessage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';

			# Initiate Product Object
			$message = new Message();

			# Get Message
			if (! $message->get($_REQUEST['target'])) {
				$this->app_error($message->error,__FILE__,__LINE__);
				$this->error("Application error");
			}
			if (! $message->id)
			$this->error("Unable to find matching message");

			# Purge Cache for message
			$message->purge_cache($message->id);

			# Error Handling
			if ($message->error) $this->error($message->error);
			else{
				$this->response->message = "Success";
				$this->response->success = 1;
			}

			api_log('content',$_REQUEST,$this->response);
			# Send Response
			print $this->formatOutput($this->response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}

		###################################################
		### Get Metadata for current view				###
		###################################################
		public function getMetadata() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();

			# Initiate Metadata Object
			$_metadata = new \Site\Page\Metadata();

			# Find Matching Views
			$metadata = $_metadata->get(
				$_REQUEST['module'],
				$_REQUEST['view'],
				$_REQUEST['index']
			);

			# Error Handling
			if ($_metadata->error) $this->error($_metadata->error);
			else{
				$response->metadata = $metadata;
				$response->success = 1;
			}

			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}
		###################################################
		### Get Metadata for current view				###
		###################################################
		public function findMetadata() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();

			# Initiate Metadata Object
			$metadataList = new \Site\Page\MetadataList();

			# Find Matching Views
			$metadata = $metadataList->find(
				array (
					'id'		=> $_REQUEST['id'],
					'module'	=> $_REQUEST['module'],
					'view'		=> $_REQUEST['view'],
					'index'		=> $_REQUEST['index'],
				)
			);

			# Error Handling
			if ($metadataList->error) $this->error($metadataList->error);
			else{
				$response->metadata = $metadata;
				$response->success = 1;
			}

			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}
		###################################################
		### Add Page Metadata							###
		###################################################
		public function addMetadata() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('edit page metadata'));

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();

			# Initiate Metadata Object
			$page = new \Site\Page();

			# Find Matching Threads
			$page->setMetadata($_REQUEST['key'], $_REQUEST['value']);

			# Error Handling
			if ($page->error) $this->error($page->error);
			else{
				$response->success = 1;
			}

			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}
		###################################################
		### Update Page Metadata						###
		###################################################
		public function updateMetadata() {
			return $this->addMetadata();
		}

		###################################################
		### Get Details regarding Specified Product		###
		###################################################
		public function findNavigationItems() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.navigationitems.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$menuList = new \Navigation\MenuList();

			# Find Matching Threads
			$items = $menuList->find(
				array (
					'id'			=> $_REQUEST['id'],
					'parent_id'		=> $_REQUEST['parent_id'],
				)
			);

			# Error Handling
			if ($menuList->error) $this->error($menuList->error);
			else{
				$response->item = $items;
				$response->success = 1;
			}

			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}

		protected function confirm_customer() {
			if (! $GLOBALS['_SESSION_']->customer->can('edit content messages')) {
				$this->error = "You do not have permissions for this task.";
				return 0;
			}
		}

		public function _methods() {
			return array(
				'ping'			=> array(),
				'parse'			=> array(
					'string'	=> array(),
				),
				'findMessages'	=> array(
					'name'		=> array(),
					'options'	=> array(),
				),
				'searchMessages'	=> array(
					'string'		=> array(),
				),
				'getMessage'	=> array(
					'target'	=> array('required' => true),
				),
				'addMessage'	=> array(
					'target'	=> array('required' => true),
					'name'		=> array(),
					'title'		=> array(),
					'content'	=> array('required' => true,'type' => 'textarea'),
					'custom_1'	=> array(),
					'custom_2'	=> array(),
					'custom_3'	=> array(),
				),
				'updateMessage'	=> array(
					'id'		=> array(),
					'target'	=> array(),
					'name'		=> array(),
					'title'		=> array(),
					'content'	=> array('type' => 'textarea'),
					'custom_1'	=> array(),
					'custom_2'	=> array(),
					'custom_3'	=> array(),
				),
				'purgeMessage'	=> array(
					'target'	=> array(),
				),
				'getMetadata'	=> array(
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array(),
				),
				'findMetadata'	=> array(
					'id'	=> array('required' => true),
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array(),
				),
				'addMetadata'	=> array(
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array(),
					'format'	=> array(),
					'content'	=> array(),
				),
				'updateMetadata'	=> array(
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array(),
					'format'	=> array(),
					'content'	=> array(),
				),
				'setPageMetadata'	=> array(
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
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
