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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('edit site pages');
	
			if (! $_REQUEST['module']) error("Module required");
			if (! $_REQUEST['view']) error("View required");
			if (! $_REQUEST['index']) $_REQUEST['index'] = '';
	
			$page = new \Site\Page();
			if (! $page->validModule($_REQUEST['module'])) error("Invalid module name");
			if (! $page->validView($_REQUEST['view'])) error("Invalid view name");
			if ($page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) error("Page already exists");
			$page->add($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index']);
			if ($page->errorCount()) error("Error adding page: ".$page->errorString());
	
			$response = new \HTTP\Response();
			$response->success = 1;
			$response->page = $page;
			print $this->formatOutput($response);
		}

		public function deletePage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('edit site pages');
	
			if (! $_REQUEST['module']) error("Module required");
			if (! $_REQUEST['view']) error("View required");
			if (! $_REQUEST['index']) $_REQUEST['index'] = '';
	
			$page = new \Site\Page();
			if (! $page->validModule($_REQUEST['module'])) error("Invalid module name");
			if (! $page->validView($_REQUEST['view'])) error("Invalid view name");
			if (! $page->get($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) error("Page not found");

			if (! $page->delete()) error($page->error);
	
			$response = new \HTTP\Response();
			$response->success = 1;
			print $this->formatOutput($response);
		}

		###################################################
		### Get Details regarding Specified Product		###
		###################################################
		public function addMessage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('edit content messages');
	
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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('edit content messages');
	
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

		public function purgeMetadata() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('edit content messages');
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

			if (!$page->purgeMetadata()) error($page->errorString());

			$response =  new \HTTP\Response();
			$response->success = 1;

			# Send Response
			api_log('content',$_REQUEST,$response);
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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			$this->requirePrivilege('configure site');
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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

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
		
        public function getAllSiteCounters() {
            $response = new \HTTP\Response();
            $counters = array();
            $existingKeys = $GLOBALS['_CACHE_']->counters();

            // foreach key that doesn't contain a bracket, add to the response of what can be watched as a public site counter
            foreach ($existingKeys as $key) {
                //if (preg_match('/^counter/', $key))
				array_push($counters,$key);
            }
			$response->success = 1;
			$response->counter = $counters;
			print $this->formatOutput($response);
		}

		public function getSiteCounter() {
			if (! preg_match('/^\w[\w\-\.\_]*$/',$_REQUEST['name'])) error("Invalid counter name");

			$counter = new \Site\Counter($_REQUEST['name']);
			$this->response->success = 1;
			$this->response->counter = new \stdClass();
			$this->response->counter->name = $_REQUEST['name'];
			$this->response->counter->value = $counter->get();
			print $this->formatOutput($this->response);
		}
		public function setAllSiteCounters() {
            $existingKeys = $GLOBALS['_CACHE_']->keys();
            $siteCounterWatched = new \Site\CounterWatched();

            // foreach key that doesn't contain a bracket, insert to the counters watched table
            foreach ($existingKeys as $key) {
                if (!preg_match('/\[|\]/', $key)) $siteCounterWatched->add(array('key' => $key, 'notes' => 'added via API:setAllSiteCounters()'));
            }
            $response = new \HTTP\Response();
			$response->success = $GLOBALS['_CACHE_']->keys();
			print $this->formatOutput($response);
		}
        public function incrementSiteCounter() {
			if (! preg_match('/^\w[\w\-\.\_]*$/',$_REQUEST['name'])) error("Invalid counter name");

			$counter = new \Site\Counter($_REQUEST['name']);
            if ($counter->increment()) {
                $this->response->success = 1;
                $this->response->counter->name = $counter->code();
                $this->response->counter->value = $counter->value();
            }
            else {
                $this->error($counter->error());
            }

			print $this->formatOutput($this->response);
        }

        public function addSiteHeader() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            $this->requirePrivilege('manage site headers');
            $header = new \Site\Header();
            $parameters = array(
                "name"  => $_REQUEST['name'],
                "value" => $_REQUEST['value']
            );

            $header->add($parameters);
            $response = new \HTTP\Response();
            $response->success = true;
            $response->header = $header;
            print $this->formatOutput($response);
        }

        public function getSiteHeader() {
            $header = new \Site\Header();
            if (!$header->get($_REQUEST['name'])) $this->error($header->error());
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->header = $header;
            print $this->formatOutput($response);
        }

        public function updateSiteHeader() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

            $this->requirePrivilege('manage site headers');
            $header = new \Site\Header();
            if (!$header->get($_REQUEST['name'])) $this->error($header->error());
            $header->update(array("value" => $_REQUEST['value']));
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->header = $header;
            print $this->formatOutput($response);
        }

        public function findSiteHeaders() {
            $headerList = new \Site\HeaderList();
            $headers = $headerList->find();
            if ($headerList->error()) error($headerList->error());
            $response = new \HTTP\Response();
            $response->success = 1;
            $response->header = $headers;
            print $this->formatOutput($response);
        }

		public function getSiteStatus() {
			$this->requirePrivilege("monitor site status");

			$cache = $GLOBALS['_CACHE_'];
			$database = new \Database\Service();

			$connection_counter = new \Site\Counter("site.connections");
			$sql_error_counter = new \Site\Counter("sql.errors");
			$denied_counter = new \Site\Counter("permission_denied");
			$counter_404 = new \Site\Counter("return404");
			$counter_403 = new \Site\Counter("return403");
			$auth_failed_counter = new \Site\Counter("auth_failed");
			$auth_blocked_counter = new \Site\Counter("auth_blocked");

			$this->response->success = 1;
			$this->response->counter->connections = $connection_counter->get();
			$this->response->counter->sql_errors = $sql_error_counter->get();
			$this->response->counter->permission_denied = $denied_counter->get();
			$this->response->counter->code_404 = $counter_404->get();
			$this->response->counter->code_403 = $counter_403->get();
			$this->response->counter->auth_failed = $auth_failed_counter->get();
			$this->response->counter->auth_blocked = $auth_blocked_counter->get();
			$this->response->cache = $cache->stats();
			$this->response->database->version = $database->version();
			$this->response->database->uptime = $database->global('uptime');
			$this->response->database->queries = $database->global('queries');
			$this->response->database->slow_queries = $database->global('slow_queries');
			$this->response->apache->version = apache_get_version();

            print $this->formatOutput($this->response);
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
				'deletePage'	=> array(
					'module'	=> array('required' => true),
					'view'		=> array('required' => true),
					'index'		=> array('required' => true),
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
			    ),
			    'getAllSiteCounters' => array(),
			    'setAllSiteCounters' => array(),
                'incrementSiteCounter' => array(
                    'name'  => array('required' => true),
                ),
				'getSiteCounter' => array(
					'name'	=> array('required' => true)
				),
                'addSiteHeader' => array(
                    'name'  => array('required' => true),
                    'value' => array('required' => true)
                ),
                'getSiteHeader' => array(
                    'name'  => array('required' => true)
                ),
                'updateSiteHeader' => array(
                    'name'  => array('required' => true),
                    'value' => array('required' => true)
                ),
                'findSiteHeaders' => array(
                    'name'  => array('required' => true),
                    'value' => array('required' => true)
				),
				'getSiteStatus' => array()
			);		
		}
	}
