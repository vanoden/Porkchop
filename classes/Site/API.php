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
			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) error("Permission denied");
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
			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) error("Permission denied");
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
			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) error("Permission denied");
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
		
        public function addSiteMessage() {
	        $siteMessage = new \Site\SiteMessage();
	        $response = new \HTTP\Response();

			if (empty($_REQUEST['user_created'])) $_REQUEST['user_created'] = $GLOBALS['_SESSION_']->customer->id;
			if (empty($_REQUEST['date_created'])) $_REQUEST['date_created'] = get_mysql_date('now');
			if (empty($_REQUEST['important'])) $_REQUEST['important'] = 0;
            if (empty($_REQUEST['subject'])) $_REQUEST['subject'] = '';
			if (empty($_REQUEST['parent_id'])) $_REQUEST['parent_id'] = 0;

            $success = $siteMessage->add(
                 array(
                  'user_created' => $_REQUEST['user_created'],
                  'date_created' => $_REQUEST['date_created'],
                  'important' => $_REQUEST['important'],
                  'subject' => $_REQUEST['subject'],
                  'content' => $_REQUEST['content'],
                  'parent_id' => $_REQUEST['parent_id']
                 )
             );
            if (!$success) {
                $response->success = 0;
                $response->error = "Site Message could not be added: ".$siteMessage->error();
            } else {
                $response->success = 1;
				$response->id = $siteMessage->id;
                $response->message = "Site Message was added successfully";
            }
        	print $this->formatOutput($response);
	    }
	    
        public function editSiteMessage() {
	        $siteMessage = new \Site\SiteMessage($_REQUEST['id']);
	        $response = new \HTTP\Response();
            $success = $siteMessage->update(
                 array(
                  'id' => $_REQUEST['id'],
                  'user_created' => $_REQUEST['user_created'],
                  'date_created' => $_REQUEST['date_created'],
                  'important' => $_REQUEST['important'],
                  'subject' => $_REQUEST['subject'],
                  'content' => $_REQUEST['content'],
                  'parent_id' => $_REQUEST['parent_id']
                 )
            );            
            if (!$success) {
                $response->success = 0;
                $response->error = "Site Message could not be edited";
            } else {
                $response->success = 1;
                $response->message = "Site Message was edited successfully";
            }
        	print $this->formatOutput($response);
        }
        
        public function removeSiteMessage() {
            $siteMessage = new \Site\SiteMessage($_REQUEST['id']);
	        $response = new \HTTP\Response();
            $success = $siteMessage->delete();
            if (!$success) {
                $response->success = 0;
                $response->error = "Site Message could not be deleted";
            } else {
                $response->success = 1;
                $response->message = "Site Message was deleted successfully";
            }
        	print $this->formatOutput($response);  
        }
        
        public function acknowledgeSiteMessageByUserId() {
            $siteMessages = new \Site\SiteMessagesList();
            $siteMessagesList = $siteMessages->find(array('user_created' => $_REQUEST['user_created']));
            foreach ($siteMessagesList as $siteMessage) {
                $siteMessageMetaData = new \Site\SiteMessageMetaData();
                $success = $siteMessageMetaData->add(
                     array(
                      'item_id' => $siteMessage->id,
                      'label' => 'acknowledged',
                      'value' => 'true'
                     )
                 );
            }        
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->message = 'all messages for user ' . $_REQUEST['user_created'] . ' have been acknowledged.';
        	print $this->formatOutput($response);
	    }

        public function acknowledgeSiteMessage() {
            $siteMessageDelivery = new \Site\SiteMessageDelivery();
            if (! $siteMessageDelivery->get($_REQUEST['message_id'],$GLOBALS['_SESSION_']->customer->id)) $this->error("Message not found");
            if (! $siteMessageDelivery->acknowledge()) $this->error($siteMessageDelivery->error());
            $response = new \HTTP\Response();
            $response->success = 1;
        	print $this->formatOutput($response);
        }

		public function getSiteMessage() {
			$siteMessage = new \Site\SiteMessage($_REQUEST['id']);
			$response = new \HTTP\Response();
			if ($siteMessage->id) {
				$response->success = 1;
				$response->message = $siteMessage;
			}
			else {
				$response->success = 0;
				$response->error = $siteMessage->error();
			}
			print $this->formatOutput($response);
		}
        
        public function addSiteMessageMetaData() {
	        $siteMessageMetaData = new \Site\SiteMessageMetaData();
	        $response = new \HTTP\Response();
            $success = $siteMessageMetaData->add(
                 array(
                  'item_id' => $_REQUEST['item_id'],
                  'label' => $_REQUEST['label'],
                  'value' => $_REQUEST['value']
                 )
             );
            if (!$success) {
                $response->success = 0;
                $response->error = "Site Message MetaData could not be added: ".$siteMessageMetaData->error();
            } else {
                $response->success = 1;
				$response->id = $siteMessageMetaData->item_id;
                $response->message = "Site Message MetaData was added successfully";
            }
        	print $this->formatOutput($response);
	    }
	            
        public function editSiteMessageMetaData() {
	        $SiteMessageMetaDataList = new \Site\SiteMessageMetaDataList();
	        $siteMessageMetaDataListArray = $SiteMessageMetaDataList->find(array('item_id' => $_REQUEST['item_id'], 'label' => $_REQUEST['label']));
	        $response = new \HTTP\Response();
	        $isSuccessful = false;
	        foreach ($siteMessageMetaDataListArray as $siteMessageMetaData) {
                $success = $siteMessageMetaData->update(
                     array(
                      'item_id' => $_REQUEST['item_id'],
                      'label' => $_REQUEST['label'],
                      'value' => $_REQUEST['value']
                     )
                );
                if (!$success) $isSuccessful = false;
	        }
            if (!$isSuccessful) {
                $response->success = 0;
                $response->error = "Site Message MetaData could not be edited";
            } else {
                $response->success = 1;
                $response->message = "Site Message MetaData was edited successfully";
            }
        	print $this->formatOutput($response);
        }
        
        public function removeSiteMessageMetaData() {
	        $SiteMessageMetaDataList = new \Site\SiteMessageMetaDataList();
	        $siteMessageMetaDataListArray = $SiteMessageMetaDataList->find(array('item_id' => $_REQUEST['item_id'], 'label' => $_REQUEST['label']));
	        $response = new \HTTP\Response();
	        $isSuccessful = false;
	        foreach ($siteMessageMetaDataListArray as $siteMessageMetaData) {
                $success = $siteMessageMetaData->delete();
                if (!$success) $isSuccessful = false;
	        }
            if (!$isSuccessful) {
                $response->success = 0;
                $response->error = "Site Message MetaData could not be deleted";
            } else {
                $response->success = 1;
                $response->message = "Site Message MetaData was deleted successfully";
            }
        	print $this->formatOutput($response);  
        }

        public function getSiteMessageMetaDataListByItemId () {
	        $SiteMessageMetaDataList = new \Site\SiteMessageMetaDataList();
	        $siteMessageMetaDataListArray = $SiteMessageMetaDataList->getListByItemId($_REQUEST['item_id']);
	        $response = new \HTTP\Response();
	        
            if (empty($siteMessageMetaDataListArray)) {
                $response->success = 0;
                $response->error = "Site Message MetaData List could not be found for item_id: " . $_REQUEST['item_id'];
            } else {
                $response->success = 1;
                $response->message = $siteMessageMetaDataListArray;
            }
        	print $this->formatOutput($response);  
        }

        public function addSiteMessageDelivery() {
	        $siteMessageDelivery = new \Site\SiteMessageDelivery();

			$params = array();
			if (isset($_REQUEST['to'])) {
				$to = new \Register\Customer();
				if ($to->get($_REQUEST['to'])) {
					$params['user_id'] = $to->id;
				}
				else {
					$this->error("to not found");
				}
			}
			elseif (isset($_REQUEST['user_id'])) {
				$params['user_id'] = $_REQUEST['user_id'];
			}
			else {
				$params['user_id'] = $GLOBALS['_SESSION_']->customer->id;
			}

			if ($GLOBALS['_SESSION_']->customer->can('send admin in-site message')) {
				# OK
			}
			elseif ($GLOBALS['_SESSION_']->customer->id == $params['user_id']) {
				# OK
			}
			else {
				$this->deny();
			}

			if (isset($_REQUEST['message_id'])) {
				$params['message_id'] = $_REQUEST['message_id'];
			}
			else {
				$this->error("message_id required");
			}

	        $response = new \HTTP\Response();
            $success = $siteMessageDelivery->add($params);

            if (!$success) {
                $response->success = 0;
                $response->error = "Site Message could not be added: ".$siteMessageDelivery->error();
            } else {
                $response->success = 1;
				$response->id = $siteMessageDelivery->id;
                $response->message = "Site Message was added successfully";
            }
        	print $this->formatOutput($response);
	    }
	    
        public function editSiteMessageDelivery() {
            $siteMessageDelivery = new \Site\SiteMessageDelivery($_REQUEST['id']);            
	        $response = new \HTTP\Response();
            $success = $siteMessageDelivery->update(
                 array(
                  'id' => $_REQUEST['id'],
                  'message_id' => $_REQUEST['message_id'],
                  'user_id' => $_REQUEST['user_id'],
                  'date_viewed' => $_REQUEST['date_viewed'],
                  'date_acknowledged' => $_REQUEST['date_acknowledged']
                 )
            );
            if (!$success) {
                $response->success = 0;
                $response->error = "Site Message could not be edited";
            } else {
                $response->success = 1;
                $response->message = "Site Message was edited successfully";
            }
        	print $this->formatOutput($response);
        }
        
        public function removeSiteMessageDelivery() {
            $siteMessageDelivery = new \Site\SiteMessageDelivery($_REQUEST['id']);  
	        $response = new \HTTP\Response();
            $success = $siteMessageDelivery->delete();
            if (!$success) {
                $response->success = 0;
                $response->error = "Site Message could not be deleted";
            } else {
                $response->success = 1;
                $response->message = "Site Message was deleted successfully";
            }
        	print $this->formatOutput($response);  
        }

		public function findSiteMessageDeliveries() {
			$deliveryList = new \Site\SiteMessageDeliveryList();
			$params = array();
			if (!empty($_REQUEST['from'])) {
				$from = new \Register\Customer();
				if ($from->get($_REQUEST['from'])) {
					$params['user_created'] = $from->id;
				}
				else {
					$this->error("from not found");
				}
			}
			if (!empty($_REQUEST['to'])) {
				$to = new \Register\Customer();
				if ($to->get($_REQUEST['to'])) {
					$params['user_id'] = $to->id;
				}
				else {
					$this->error("to not found");
				}
			}
			if (!empty($_REQUEST['viewed'])) {
				if (preg_match('/^(1|true)$/',$_REQUEST['viewed'])) {
					$params['viewed'] = true;
				}
				elseif (preg_match('/^(0|false)$/',$_REQUEST['viewed'])) {
					$params['viewed'] = false;
				}
				else {
					$this->error("Cannot understand 'viewed' param");
				}
			}
			if (!empty($_REQUEST['acknowledged'])) {
				if (preg_match('/^(1|true)$/',$_REQUEST['acknowledged'])) {
					$params['acknowledged'] = true;
				}
				elseif (preg_match('/^(0|false)$/',$_REQUEST['acknowledged'])) {
					$params['acknowledged'] = false;
				}
				else {
					$this->error("Cannot understand 'acknowledged' param");
				}
			}
			$deliveries = $deliveryList->find($params);
			if ($deliveryList->error()) $this->error($deliveryList->error());
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->delivery = $deliveries;
			print $this->formatOutput($response);
		}

		public function mySiteMessageCount() {
			$deliveryList = new \Site\SiteMessageDeliveryList();
			$params = array();
			$params['user_id'] = $GLOBALS['_SESSION_']->customer->id;
			$params['acknowledged'] = false;
			$deliveries = $deliveryList->find($params);
			if ($deliveryList->error()) $this->error($deliveryList->error());
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->count = $deliveryList->count();
			print $this->formatOutput($response);
		}

		public function timestamp() {
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->timestamp = time();
			print $this->formatOutput($response);
		}
 
		public function search() {
			$response = new \HTTP\Response();
			$response->success = 1;
	        $messageList = new \Content\MessageList();
	        $messages = $messageList->search(array('string'=>$_REQUEST['string'], 'is_user_search' => true));
	        $response->count = count($messages);
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
				'addSiteMessage'	=> array(
                    'user_created' => array('required' => true),
                    'date_created' => array('required' => true),
                    'important' => array('required' => true),
                    'content' => array('required' => true),
                    'parent_id' => array(),                    
                 ),   
				'editSiteMessage'	=> array(
                    'id' => array('required' => true),
                    'user_created' => array('required' => true),
                    'date_created' => array('required' => true),
                    'important' => array('required' => true),
                    'content' => array('required' => true),
                    'parent_id' => array(),
                 ), 
                 'removeSiteMessage'	=> array(
                    'id' => array('required' => true)
			     ),
				'getSiteMessage'	=> array(
					'id'	=> array('required' => true)
				),
				'addSiteMessageDelivery'	=> array(
                    'message_id' => array('required' => true),
                    'user_id' => array('required' => true),
                    'date_viewed' => array('required' => true),
                    'date_acknowledged' => array('required' => true)
                 ),   
				'editSiteMessageDelivery'	=> array(
                    'id' => array('required' => true),
                    'message_id' => array('required' => true),
                    'user_id' => array('required' => true),
                    'date_viewed' => array('required' => true),
                    'date_acknowledged' => array('required' => true)
                 ), 
				 'getSiteMessageMetaDataListByItemId'	=> array(
					'item_id'	=> array('required' => true)
				 ),
                 'removeSiteMessageDelivery'	=> array(
                    'id' => array('required' => true)
			     ),
				'findSiteMessageDeliveries'	=> array(
					'user_created'	=> array(),
					'user_delivered'	=> array(),
					'viewed'			=> array(),
					'acknowledged'		=> array(),
				),
				'mySiteMessageCount' => array(),
				 'addSiteMessageMetaData'	=> array(
                    'item_id' => array('required' => true),
                    'label' => array('required' => true),
                    'value' => array('required' => true),
                 ), 
				 'editSiteMessageMetaData'	=> array(
                    'item_id' => array('required' => true),
                    'label' => array('required' => true),
                    'value' => array('required' => true),
                 ), 
                 'removeSiteMessageMetaData'	=> array(
                    'item_id' => array('required' => true)
			     ),
                 'acknowledgeSiteMessageByUserId'	=> array(
                    'user_created' => array('required' => true)
			     ),
				 'timestamp' => array(),
                 'search'	=> array(
                    'string' => array('required' => true)
			     )
			);
		}
	}
