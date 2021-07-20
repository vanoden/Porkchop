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
		### Get Details regarding Specified Message		###
		###################################################
		public function getMessage() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();

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
				$response->request = $_REQUEST;
				$response->message = $message;
				$response->success = 1;
			}

			api_log('content',$_REQUEST,$response);

			# Send Response
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}
		###################################################
		### Get Details regarding Specified Product		###
		###################################################
		public function addMessage() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$content = new \Content\Message();

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
			if ($content->error) error($content->error);
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
			# Default StyleSheet
			if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$message = new \Content\Message();
			if (isset($_REQUEST['id'])) {
				$message->id = $_REQUEST['id'];
				$message->details();
			}
			elseif (isset($_REQUEST['target'])) {
				$message->get($_REQUEST['target']);
			}
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
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$_content = new Content();

			# Get Message
			$message = $_content->get($_REQUEST['target']);
			if ($_content->error)			{
				$this->app_error($_content->error,__FILE__,__LINE__);
				$this->error("Application error");
			}
			if (! $message->id)
			$this->error("Unable to find matching message");

			# Purge Cache for message
			$_content->purge_cache($message->id);

			# Error Handling
			if ($_content->error) error($_content->error);
			else{
				$response->message = "Success";
				$response->success = 1;
			}

			api_log('content',$_REQUEST,$response);
			# Send Response
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
			$_metadata = new \Site\Page\Metadata();

			# Find Matching Views
			$metadata = $_metadata->find(
				array (
					'id'		=> $_REQUEST['id'],
					'module'	=> $_REQUEST['module'],
					'view'		=> $_REQUEST['view'],
					'index'		=> $_REQUEST['index'],
				)
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
		### Add Page Metadata							###
		###################################################
		public function addMetadata() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();

			# Initiate Metadata Object
			$_metadata = new \Site\Page\Metadata();

			# Find Matching Threads
			$metadata = $_metadata->add(
				array(
					'module'		=> $_REQUEST['module'],
					'view'			=> $_REQUEST['view'],
					'index'			=> $_REQUEST['index'],
					'format'		=> $_REQUEST['format'],
					'content'		=> $_REQUEST['content']
				)
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
		### Update Page Metadata						###
		###################################################
		public function updateMetadata() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();

			# Initiate Metadata Object
			$_metadata = new \Site\Page\Metadata();

			# Find Metadata On Key
			$current = $_metadata->get(
				array(
					'module'		=> $_REQUEST['module'],
					'view'			=> $_REQUEST['view'],
					'index'			=> $_REQUEST['index'],
				)
			);
			if ($current->id) {
				$response->message = "Updating id ".$current->id;
				# Find Matching Threads
				$metadata = $_metadata->update(
					$current->id,
					array(
						'format'		=> $_REQUEST['format'],
						'content'		=> $_REQUEST['content']
					)
				);
			}
			else {
				$this->error("Could not find matching object");
			}

			# Error Handling
			if ($_metadata->error) error($_metadata->error);
			else{
				#$response->request = $_REQUEST;
				$response->metadata = $metadata;
				$response->success = 1;
			}

			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}
		###################################################
		### Get Details regarding Specified Product		###
		###################################################
		public function findNavigationItems() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.navigationitems.xsl';
			$response = new \HTTP\Response();

			# Initiate Product Object
			$_menu = new Menu();

			# Find Matching Threads
			$items = $_menu->find(
				array (
					'id'			=> $_REQUEST['id'],
					'parent_id'		=> $_REQUEST['parent_id'],
				)
			);

			# Error Handling
			if ($_menu->error) $this->error($_menu->error);
			else{
				$response->item = $items;
				$response->success = 1;
			}

			# Send Response
			api_log('content',$_REQUEST,$response);
			print $this->formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
		}

		protected function confirm_customer() {
			if (! $GLOBALS['_SESSION_']->customer->has_role('content reporter')) {
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
				'getMessage'	=> array(
					'target'	=> array('required' => true),
				),
				'addMessage'	=> array(
					'target'	=> array('required' => true),
					'name'		=> array(),
					'title'		=> array(),
					'content'	=> array('required' => true),
					'custom_1'	=> array(),
					'custom_2'	=> array(),
					'custom_3'	=> array(),
				),
				'updateMessage'	=> array(
					'id'		=> array(),
					'target'	=> array(),
					'name'		=> array(),
					'title'		=> array(),
					'content'	=> array(),
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
