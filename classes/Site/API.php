<?php
	namespace Site;

	/* Base Class for APIs */
	class API extends \API {

		public function __construct() {
			$this->_name = 'site';
			$this->_version = '0.1.2';
			$this->_release = '2024-12-06';
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

			$response = new \APIResponse();
			# Error Handling
			if ($page_list->error()) $this->error($page_list->error());
			else{
				$response->addElement('page',$pages);
			}

			# Send Response
			$response->print();
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
			if (isset($_REQUEST['module'])) $page->getPage($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index']);
			elseif (isset($_REQUEST['target'])) $page->getPage('content','index',$_REQUEST['target']);
			else {
				print_r($GLOBALS['_REQUEST_']->query_vars);
				$_REQUEST['module'] = $GLOBALS['_REQUEST_']->query_vars[0];
				$_REQUEST['view'] = $GLOBALS['_REQUEST_']->query_vars[1];
				$_REQUEST['index'] = $GLOBALS['_REQUEST_']->query_vars[2];
			}

			# Error Handling
			if ($page->error()) error($page->error());
			if (!$page->exists()) $this->notFound("Page not found");

			$response = new \APIResponse();
			$response->addElement('page',$page);
			$response->print();
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
			if ($page->getPage($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) error("Page already exists");
			$page->add($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index']);
			if ($page->errorCount()) error("Error adding page: ".$page->errorString());
	
			$response = new \APIResponse();
			$response->addElement('page',$page);
			$response->print();
		}

		public function deletePage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('edit site pages');
	
			if (! $_REQUEST['module']) error("Module required");
			if (! $_REQUEST['view']) error("View required");
			if (! $_REQUEST['index']) $_REQUEST['index'] = '';
	
			$page = new \Site\Page();
			if (! $page->validModule($_REQUEST['module'])) $this->error("Invalid module name");
			if (! $page->validView($_REQUEST['view'])) $this->error("Invalid view name");
			if (! $page->getPage($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) $this->notFound("Page not found");

			if (! $page->delete()) $this->error($page->error());

			$response = new \APIResponse();
			$response->print();
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
			$content = new \Content\Message();
	
			# Find Matching Threads
			$content->add(
				array (
					'name'			=> $_REQUEST['name'],
					'target'		=> $_REQUEST['target'],
					'title'			=> $_REQUEST['title'],
					'content'		=> $_REQUEST['content']
				)
			);

			# Error Handling
			if ($content->error()) $this->error($content->error());
	
			$response = new \APIResponse();
			$response->addElement('message',$content);
			$response->print();
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
			if ($content->error()) $this->error($content->error());

			$response = new \APIResponse();
			$response->addElement('content',$content);
			$response->print();
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
			if (! $message->get($_REQUEST['target'])) {
				if ($message->error()) $this->error($message->error());
				else $this->app_error($message->error(),__FILE__,__LINE__);
			}

			# Purge Cache for message
			$message->purge_cache();

			$response = new \APIResponse();
			$response->print();
		}
	
		/**
		 * Find messages matching specific criteria
		 * @return void
		 */
		public function findMessages() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'site.messages.xsl';
	
			# Initiate Product Object
			$message_list = new \Site\SiteMessagesList();
	
			# Find Matching Threads
			$messages = $message_list->find(
				array (
					'send_user_id'		=> $_REQUEST['send_user_id'],
					'receive_user_id'	=> $_REQUEST['receive_user_id'],
					'acknowledged'		=> $_REQUEST['acknowledged']
				)
			);
	
			# Error Handling
			if ($message_list->error()) $this->error($message_list->error());

			$response = new \APIResponse();
			$response->addElement('message',$messages);
			$response->print();
		}

		###################################################
		### Get Metadata for current view				###
		###################################################
		public function getPageMetadata() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();
	
			$page = new \Site\Page();
			if (! $page->getPage($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) $this->notFound();

			$metadata = $page->getMetadata($_REQUEST['key']);
			if ($page->error()) $this->app_error("Cannot get metadata: ".$page->error());

			$response = new \APIResponse();
			$response->addElement('metadata',$metadata);
			$response->print();
		}

		###################################################
		### Get Metadata for current view				###
		###################################################
		public function findPageMetadata() {
			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.metadata.xsl';
			$response = new \HTTP\Response();
	
			$page = new \Site\Page();
			if (!$page->getPage($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) $this->notFound();

			# Initiate Metadata Object
			$metadata = $page->getAllMetadata();

			$response = new \APIResponse();
			$response->addElement('metadata',$metadata);
			$response->print();
		}

		public function setPageMetadata() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('edit content messages');
	
			$response = new \HTTP\Response();
	
			$page = new \Site\Page();
			if (! $page->getPage($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) $this->notFound();

			if (! $page->setMetadata($_REQUEST['key'],$_REQUEST['value'])) $this->error($page->error());

			$response = new \APIResponse();
			$response->addElement('metadata',array('key' => $_REQUEST['key'],'value' => $_REQUEST['value']));
			$response->print();
		}

		public function purgeMetadata() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('edit content messages');
			$response = new \HTTP\Response();
	
			$page = new \Site\Page();
			if (!$page->getPage($_REQUEST['module'],$_REQUEST['view'],$_REQUEST['index'])) $this->notFound();
			if (!$metadata = $page->getAllMetadata($_REQUEST['key'])) $this->app_error($page->error());
			if (!$page->purgeMetadata()) $this->error($page->errorString());


			$response = new \APIResponse();
			$response->addElement('metadata',$metadata);
			$response->print();
		}

		###################################################
		### Get List of Site Navigation Menus			###
		###################################################
		public function findNavigationMenus() {
			# Initiate Product Object
			$menulist = new \Site\Navigation\MenuList();
	
			# Find Matching Threads
			$menus = $menulist->find(
				array (
					'id'			=> $_REQUEST['id'],
					'parent_id'		=> $_REQUEST['parent_id'],
				)
			);
	
			# Error Handling
			if ($menulist->error()) $this->error($menulist->error());

			$response = new \APIResponse();
			$response->addElement('menu',$menus);
			$response->print();
		}

		###################################################
		### Get Menu									###
		###################################################
		function getNavigationMenu() {
			$parameters = array();

			$response = new \APIResponse();

			if (!empty($_REQUEST['code'])) {
				$menu = new \Site\Navigation\Menu();
				if ($menu->get($_REQUEST['code'])) {
					$response->AddElement('request',$_REQUEST);
					$response->AddElement('menu',$menu);
					$response->success(true);
				}
				elseif ($menu->error()) {
					$this->error($menu->error());
				}
				else {
					$this->notFound("Menu not found");
				}
			}
			else $this->invalidRequest("menu code required");

			# Send Response
			$response->print();
		}

		###################################################
		### Add Menu									###
		###################################################
		function addNavigationMenu() {
			$parameters = array();

			$menu = new \Site\Navigation\Menu();

			if (! isset($_REQUEST['code'])) $this->error("code required");
			if (! $menu->validCode($_REQUEST['code'])) $this->error("Invalid Code");
			if (! $menu->validTitle($_REQUEST['title'])) $this->error("Invalid Title");

			$parameters['code'] = $_REQUEST['code'];
			$parameters['title'] = $_REQUEST['title'];
			$response = new \APIResponse();
			if ($menu->add($parameters)) {
				$response->AddElement('menu',$menu);
				$response->success(true);
			}
			else {
				$this->error($menu->error());
			}

			# Send Response
			$response->print();
		}

		###################################################
		### Get Menu Items								###
		###################################################
		function findNavigationItems() {
			$parameters = array();
			$itemlist = new \Site\Navigation\ItemList();

			if (!empty($_REQUEST['menu_code'])) {
				$menu = new \Site\Navigation\Menu();
				if ($menu->get($_REQUEST['menu_code'])) {
					$parameters['menu_id'] = $menu->id;
					if (isset($_REQUEST['parent_id'])) {
						$parameters['parent_id'] = $_REQUEST['parent_id'];
					}
				}
				elseif ($menu->error()) {
					$this->error($menu->error());
				}
				else {
					$this->invalidRequest("Menu '".$_REQUEST['menu_code']."' not found");
				}
			}
			if (!empty($_REQUEST['target'])) {
				$item = new \Site\Navigation\Item();
				if ($item->validTarget($_REQUEST['target']))
					$parameters['target'] = $_REQUEST['target'];
				else $this->error("Invalid target");
			}

			$response = new \APIResponse();
			$items = $itemlist->find($parameters);
			if ($itemlist->error()) $this->error($itemlist->error());
			else {
				$response->AddElement('item',$items);
				$response->AddElement('count',$itemlist->count());
				$response->success(true);
			}

			# Send Response
			$response->print();
		}

		###################################################
		### Add Menu Item								###
		###################################################
		function addNavigationItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Default StyleSheet
			if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.item.xsl';

			$parameters = array();

			if (! isset($_REQUEST['menu_code'])) $this->error("menu_code required");
			$menu = new \Site\Navigation\Menu();
			if (! $menu->get($_REQUEST['menu_code'])) $this->error("Menu not found");			

			if (!empty($_REQUEST['parent_code'])) {
				$parent_item = new \Site\Navigation\Item();
				$parent_item->getItem($menu->id,$_REQUEST['parent_code']);
				if ($parent_item->error()) $this->error("Error finding parent menu item: ".$parent_item->error());
				if (! $parent_item->exists()) $this->invalidRequest("Parent Menu Item not found");
				$parameters['parent_id'] = $parent_item->id;
			}

			$parameters['menu_id'] = $menu->id;	
			$parameters['title'] = $_REQUEST['title'];
			$parameters['target'] = $_REQUEST['target'];
			$parameters['alt'] = $_REQUEST['alt'];
			$parameters['description'] = $_REQUEST['description'];
			$parameters['view_order'] = $_REQUEST['view_order'];
			$parameters['parent_id'] = isset($parent_item) ? $parent_item->id : null;

			$response = new \APIResponse();
			$item = new \Site\Navigation\Item();
			if ($item->add($parameters)) {
				$response->AddElement('item',$item);
				$response->success(true);
				$response->addElement('item',$item);
			}
			elseif ($item->error()) {
				$this->error($item->error());
			}

			# Send Response
			$response->print();
		}

		###################################################
		### Update Menu Item							###
		###################################################
		function updateNavigationItem() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			# Default StyleSheet
			if (empty($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'navigation.item.xsl';

			$parameters = array();

			if (!empty($_REQUEST['title']) && !empty($_REQUEST['menu_code'])) {
				$menu = new \Site\Navigation\Menu();
				$item = $menu->item($_REQUEST['title']);
				if ($item->exists()) $_REQUEST['id'] = $item->id;
				else $this->error("Item not found");
			}
			if (! isset($_REQUEST['id'])) $this->error("id required");
			$item = new \Site\Navigation\Item($_REQUEST['id']);
			if ($item->error()) $this->error($item->error());

			if (! $item->id) $this->error("Item not found");

			$parameters['title'] = $_REQUEST['title'];
			$parameters['target'] = $_REQUEST['target'];
			$parameters['alt'] = $_REQUEST['alt'];
			$parameters['description'] = $_REQUEST['description'];
			$parameters['view_order'] = $_REQUEST['view_order'];

			$response = new \APIResponse();
			if ($item->update($parameters)) {
				$response->AddElement('item',$item);
			}
			elseif ($item->error()) {
				$this->error($item->error());
			}

			# Send Response
			$response->print();
		}

		/** @method getNavigationItem
		 * Get Menu Item Details
		 * @param code Title of the Menu to which it belongs
		 * @param title Title of the Menu Item
		 * @param parent_code (optional) Code of Parent Menu Item
		 * @return void
		 */
		function getNavigationItem() {
			$response = new \APIResponse();
			$menu = new \Site\Navigation\Menu();
			$menu->get($_REQUEST['menu_code']);
			if ($menu->error()) $this->error("Error finding menu: ".$menu->error());
			if (! $menu->exists()) $this->invalidRequest("Menu not found");

			if (!empty($_REQUEST['parent_code'])) {
				$parent_item = new \Site\Navigation\Item();
				$parent_item->getItem($menu->id,$_REQUEST['parent_code']);
				if ($parent_item->error()) $this->error("Error finding parent menu item: ".$parent_item->error());
				if (! $parent_item->exists()) $this->invalidRequest("Parent Menu Item not found");
			}
			else {
				$parent_item = null;
			}

			$menu_item = $menu->getItem($_REQUEST['title'], isset($parent_item) ? $parent_item->id : null);
			if ($menu->error()) $this->error($menu->error());
			if (! $menu_item->exists()) $this->notFound("Menu Item not found");

			$response->AddElement('item', $menu_item);
			$response->print();
			return;
		}

		###################################################
		### Dump Menu Data for Javascript				###
		###################################################
		function navigationMenuObject() {
			$response = new \HTTP\Response();

			if (empty($_REQUEST['code'])) $this->error("menu code required");

			$menu = new \Site\Navigation\Menu();
			if (! $menu->get($_REQUEST['code'])) $this->error("menu not found");

			$response = new \APIResponse();
			$response->AddElement('item',$menu->cascade());

			# Send Response
			$response->print();
		}
		
		public function deleteConfiguration() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) error("Permission denied");
			$response = new \HTTP\Response();
			$configuration = new \Site\Configuration($_REQUEST['key']);
			if (! $configuration->delete()) $this->error($configuration->error());

			$response = new \APIResponse();
			$response->print();
		}
		
		public function setConfiguration() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			if (! $GLOBALS['_SESSION_']->customer->can('configure site')) error("Permission denied");
			$response = new \HTTP\Response();
			$configuration = new \Site\Configuration($_REQUEST['key']);
			if (! $configuration->set($_REQUEST['value'])) $this->error($configuration->error());

			$response = new \APIResponse();
			$response->addElement('configuration',$configuration);
			$response->print();
		}
		
		public function getConfiguration() {
			$this->requirePrivilege('configure site');
			$response = new \HTTP\Response();
			$configuration = new \Site\Configuration($_REQUEST['key']);
			if (! $configuration->get($_REQUEST['key'])) $this->error($configuration->error());

			$response = new \APIResponse();
			$response->addElement('configuration',array('key' => $configuration->key(),'value' => $configuration->value()));
			$response->print();
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
			if (!$success) $this->error("Site Message could not be added: ".$siteMessage->error());

			$response = new \APIResponse();
			$response->addElement('id',$siteMessage->id);
			$response->print();
		}
		
		public function editSiteMessage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$siteMessage = new \Site\SiteMessage($_REQUEST['id']);

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
			if (!$success) $this->error("Site Message could not be edited");

			$response = new \APIResponse();
			$response->print();
		}
		
		public function removeSiteMessage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$siteMessage = new \Site\SiteMessage($_REQUEST['id']);
			$response = new \HTTP\Response();
			$success = $siteMessage->delete();
			if (!$success) $this->error("Site Message could not be deleted");

			$response = new \APIResponse();
			$response->print();  
		}
		
		public function acknowledgeSiteMessageByUserId() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$siteMessages = new \Site\SiteMessagesList();
			$siteMessagesList = $siteMessages->find(array('user_created' => $_REQUEST['user_created']));
			foreach ($siteMessagesList as $siteMessage) {
				$siteMessage->acknowledge();
				 if ($siteMessage->error()) $this->error($siteMessage->error());
			}        

			$response = new \APIResponse();
			$response->print();
		}

		public function acknowledgeSiteMessage() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			$siteMessageDelivery = new \Site\SiteMessageDelivery();
			if (! $siteMessageDelivery->getDelivery($_REQUEST['message_id'],$GLOBALS['_SESSION_']->customer->id)) $this->error("Message not found");
			if (! $siteMessageDelivery->acknowledge()) $this->error($siteMessageDelivery->error());

			$response = new \APIResponse();
			$response->print();
		}

		public function getSiteMessage() {
			$siteMessage = new \Site\SiteMessage($_REQUEST['id']);
			$response = new \HTTP\Response();
			if (! $siteMessage->id) $this->error($siteMessage->error());

			$response = new \APIResponse();
			$response->addElement('message',$siteMessage);
			$response->print();
		}

		/**
		 * Add a metadata record for a site message
		 * @return void 
		 */
		public function addSiteMessageMetaData() {
			$siteMessage = new \Site\SiteMessage($_REQUEST['message_id']);
			if (! $siteMessage->exists()) $this->notFound();
			if ($siteMessage->user_created != $GLOBALS['_SESSION_']->customer->id) $this->deny();

			$response = new \HTTP\Response();
			$success = $siteMessage->setMetadata($_REQUEST['key'],$_REQUEST['value']);
			if (!$success) $this->error("Site Message MetaData could not be added: ".$siteMessage->error());

			// Just Return Success
			$response = new \APIResponse();
			$response->print();
		}

		/**
		 * Edit a metadata record for a site message
		 * @return void 
		 */
		public function editSiteMessageMetaData() {
			$siteMessage = new \Site\SiteMessage($_REQUEST['message_id']);
			if(! $siteMessage->setMetadata($_REQUEST['key'],$_REQUEST['value'])) $this->error("Site Message MetaData could not be edited");

			$response = new \APIResponse();
			$response->print();
		}
		
		/**
		 * Remove a metadata record for a site message
		 * @return void
		 */
		public function removeSiteMessageMetaData() {
			$siteMessage = new \Site\SiteMessage($_REQUEST['item_id']);
			if (!$siteMessage->unsetMetadata($_REQUEST['label'])) $this->error("Site Message MetaData could not be deleted");

			$response = new \APIResponse();
			$response->print();  
		}

		public function getSiteMessageMetaDataListByItemId () {
			$siteMessage = new \Site\SiteMessage($_REQUEST['item_id']);
			$metadata = $siteMessage->getAllMetadata();
			
			if (!$siteMessage->exists()) $this->notFound("Site Message MetaData List could not be found for item_id: " . $_REQUEST['item_id']);

			$response = new \APIResponse();
			$response->addElement('message',$siteMessage);
			$response->print();
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

			if (!$success) $this->error("Site Message could not be added: ".$siteMessageDelivery->error());

			$response = new \APIResponse();
			$response->addElement('id',$siteMessageDelivery->id);
			$response->print();
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
			if (!$success) $this->error("Site Message could not be edited");

			$response = new \APIResponse();
			$response->print();
		}
		
		public function removeSiteMessageDelivery() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$siteMessageDelivery = new \Site\SiteMessageDelivery($_REQUEST['id']);  
			$response = new \HTTP\Response();
			$success = $siteMessageDelivery->delete();
			if (!$success) $this->error("Site Message could not be deleted");

			$response = new \APIResponse();
			$response->print();  
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

			$response = new \APIResponse();
			$response->addElement('delivery',$deliveries);
			$response->print();
		}

		public function mySiteMessageCount() {
			$deliveryList = new \Site\SiteMessageDeliveryList();
			$params = array();
			$params['user_id'] = $GLOBALS['_SESSION_']->customer->id;
			$params['acknowledged'] = false;
			$deliveries = $deliveryList->find($params);
			if ($deliveryList->error()) $this->error($deliveryList->error());

			$response = new \APIResponse();
			$response->addElement('count',$deliveryList->count());
			$response->print();
		}

		public function timestamp() {
			$response = new \APIResponse();
			$response->addElement('timestamp',time());
			$response->print();
		}
 
		public function search() {
			$messageList = new \Content\MessageList();
			$messages = $messageList->search(array('string'=>$_REQUEST['string'], 'is_user_search' => true));

			$response = new \APIResponse();
			$response->addElement('count',count($messages));
			$response->print();
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
			$response = new \APIResponse();
			$response->addElement('counter',$counters);
			$response->print();
		}

		/**
		 * Get a specific counter for the site
		 * @return void 
		 */
		public function getSiteCounter() {
			if (! preg_match('/^\w[\w\-\.\_]*$/',$_REQUEST['name'])) error("Invalid counter name");

			$counter = new \Site\Counter($_REQUEST['name']);
			$cntr = new \stdClass();
			$cntr->name = $counter->code();
			$cntr->value = $counter->get();

			$response = new \APIResponse();
			$response->addElement('counter',$cntr);
			$response->print();
		}

		/**
		 * Get all counters for the site
		 * @return void 
		 */
		public function setAllSiteCounters() {
			$existingKeys = $GLOBALS['_CACHE_']->keys();
			$siteCounterWatched = new \Site\CounterWatched();

			// foreach key that doesn't contain a bracket, insert to the counters watched table
			foreach ($existingKeys as $key) {
				if (!preg_match('/\[|\]/', $key)) $siteCounterWatched->add(array('key' => $key, 'notes' => 'added via API:setAllSiteCounters()'));
			}
			$response = new \APIResponse();
			$response->addElement('keys',$GLOBALS['_CACHE_']->keys());
			$response->print();
		}

		/**
		 * Increment a specific counter for the site
		 * @return void 
		 */
		public function incrementSiteCounter() {
			if (! preg_match('/^\w[\w\-\.\_]*$/',$_REQUEST['name'])) error("Invalid counter name");

			$counter = new \Site\Counter($_REQUEST['name']);
			if ($counter->increment()) {
				$response = new \APIResponse();
				$cntr = new \stdClass();
				$cntr->name = $counter->code();
				$cntr->value = $counter->value();
				$response->addElement('counter',$cntr);
			}
			else {
				$this->error($counter->error());
			}

			$response->print();
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
			$response = new \APIResponse();
			$response->addElement('header',$header);
			$response->print();
		}

		public function getSiteHeader() {
			$header = new \Site\Header();
			if (!$header->get($_REQUEST['name'])) $this->error($header->error());

			$response = new \APIResponse();
			$response->addElement('header',$header);
			$response->print();
		}

		public function updateSiteHeader() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			$this->requirePrivilege('manage site headers');
			$header = new \Site\Header();
			if (!$header->get($_REQUEST['name'])) $this->error($header->error());
			$header->update(array("value" => $_REQUEST['value']));

			$response = new \APIResponse();
			$response->addElement('header',$header);
			$response->print();
		}

		public function findSiteHeaders() {
			$headerList = new \Site\HeaderList();
			$headers = $headerList->find();
			if ($headerList->error()) error($headerList->error());

			$response = new \APIResponse();
			$response->addElement('header',$headers);
			$response->print();
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
			$counter_500 = new \Site\Counter("return500");
			$auth_failed_counter = new \Site\Counter("auth_failed");
			$auth_blocked_counter = new \Site\Counter("auth_blocked");

			// Module Counters
			$moduleSiteCounter = new \Site\Counter("module.site");
			$moduleMonitorCounter = new \Site\Counter("module.monitor");
			$moduleSupportCounter = new \Site\Counter("module.support");
			$moduleContentCounter = new \Site\Counter("module.content");
			$moduleEngineeringCounter = new \Site\Counter("module.engineering");
			$moduleRegisterCounter = new \Site\Counter("module.register");
			$moduleProductCounter = new \Site\Counter("module.product");
			$moduleSalesCounter = new \Site\Counter("module.sales");
			$moduleGeographyCounter = new \Site\Counter("module.geography");

			$counter = new \stdClass();
			$counter->connections = $connection_counter->get();
			$counter->sql_errors = $sql_error_counter->get();
			$counter->permission_denied = $denied_counter->get();
			$counter->code_404 = $counter_404->get();
			$counter->code_403 = $counter_403->get();
			$counter->code_500 = $counter_500->get();
			$counter->auth_failed = $auth_failed_counter->get();
			$counter->auth_blocked = $auth_blocked_counter->get();
			$counter->module_site = $moduleSiteCounter->get();
			$counter->module_monitor = $moduleMonitorCounter->get();
			$counter->module_support = $moduleSupportCounter->get();
			$counter->module_content = $moduleContentCounter->get();
			$counter->module_engineering = $moduleEngineeringCounter->get();
			$counter->module_register = $moduleRegisterCounter->get();
			$counter->module_product = $moduleProductCounter->get();
			$counter->module_sales = $moduleSalesCounter->get();
			$counter->module_geography = $moduleGeographyCounter->get();

			$cache = $cache->stats();

			$db = new \stdClass();
			$db->version = $database->version();
			$db->uptime = $database->global('uptime');
			$db->queries = $database->global('queries');
			$db->slow_queries = $database->global('slow_queries');
			$db->connections = $database->global('connections');
			$db->com_select = $database->global('Com_select');
			$db->com_insert = $database->global('Com_insert');
			$db->com_update = $database->global('Com_update');
			$db->com_replace = $database->global('Com_replace');
			$db->aborted_connects = $database->global('Aborted_connects');
			$db->threads_connected = $database->global('Threads_connected');
			$db->threads_running = $database->global('Threads_running');
			$apache = new \stdClass();
			$apache->version = apache_get_version();

			$response = new \APIResponse();
			$response->addElement('counter',$counter);
			$response->addElement('cache',$cache);
			$response->addElement('database',$db);
			$response->addElement('apache',$apache);
			$response->print();
		}

		public function getNodeHealth() {

			$response = new \APIResponse();
			$response->code(200);
			$databaseStatus = 'available';
			$cacheStatus = 'available';

			// check the database
			$database = new \Database\Service();
			if (empty($database->version())) {
				$databaseStatus = 'error';
				$response->code(500);
				$counter = new \Site\Counter("response500");
				$counter->increment();
			}

			// check the cache
			$cache = $GLOBALS['_CACHE_'];
			if (empty($cache->stats())) {
				$cacheStatus = 'error';
				$response->code(500);
			}
			
			$response->addElement('cache',$cacheStatus);
			$response->addElement('database',$databaseStatus);
			$response->print();
		}

		public function getTOULatestVersion() {
			// Confirm Require Inputs
			if (empty($_REQUEST['tou_id'])) $this->invalidRequest("tou_id required");

			// Get Associated Resources
			$tou = new \Site\TermsOfUse($_REQUEST['tou_id']);
			if ($tou->error()) $this->error($tou->error());
			if (!$tou->exists()) $this->notFound();

			$version = $tou->latestVersion();
			if (!$version) $this->notFound("No published versions");

			// Prepare and Return Response
			$response = new \APIResponse();
			$response->addElement('version',$version);
			$response->print();
		}

		###################################################
		### Terms of Use									###
		###################################################
		public function addTermsOfUse() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			$this->requirePrivilege('manage terms of use');

			$params = array(
				'name' => $_REQUEST['name'] ?? '',
				'description' => $_REQUEST['description'] ?? ''
			);
			if (!empty($_REQUEST['code'])) $params['code'] = $_REQUEST['code'];

			$tou = new \Site\TermsOfUse();
			if (!$tou->add($params)) $this->error($tou->error());

			$response = new \APIResponse();
			$response->addElement('terms_of_use',$tou);
			$response->print();
		}

		public function addTermsOfUseVersion() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			$this->requirePrivilege('manage terms of use');

			$tou = new \Site\TermsOfUse();
			if (!$tou->getByCode($_REQUEST['tou_code'])) $this->invalidRequest("Terms of use not found for code: ".$_REQUEST['tou_code']);

			$version = $tou->addVersion(array('content' => $_REQUEST['content']));
			if ($tou->error()) $this->error($tou->error());
			if (!$version || !$version->id) $this->error("Failed to create version");

			if (!empty($_REQUEST['status']) && $_REQUEST['status'] == 'PUBLISHED') {
				if (!$version->publish()) $this->error($version->error());
			}

			$response = new \APIResponse();
			$response->addElement('version',$version);
			$response->print();
		}

		public function activateTermsOfUseVersion() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			$this->requirePrivilege('manage terms of use');

			$version = new \Site\TermsOfUseVersion($_REQUEST['version_id']);
			if (!$version->id) $this->notFound("Version not found");
			if (!$version->publish()) $this->error($version->error());

			$response = new \APIResponse();
			$response->addElement('version',$version);
			$response->print();
		}

		public function cancelTermsOfUseVersion() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");
			$this->requirePrivilege('manage terms of use');

			$version = new \Site\TermsOfUseVersion($_REQUEST['version_id']);
			if (!$version->id) $this->notFound("Version not found");
			if (!$version->retract()) $this->error($version->error());

			$response = new \APIResponse();
			$response->addElement('version',$version);
			$response->print();
		}

		public function findTermsOfUse() {
			$this->requirePrivilege('manage terms of use');

			$params = array();
			if (!empty($_REQUEST['name'])) $params['name'] = $_REQUEST['name'];
			if (!empty($_REQUEST['code'])) $params['code'] = $_REQUEST['code'];

			$list = new \Site\TermsOfUseList();
			$terms = $list->find($params);
			if ($list->error()) $this->error($list->error());

			$response = new \APIResponse();
			$response->addElement('terms_of_use',$terms);
			$response->print();
		}

		public function getUUID() {
			$porkchop = new \Porkchop();
			print $porkchop->uuid();
		}

		###################################################
		### Purge Expired Sessions						###
		###################################################
		public function purgeExpiredSessions() {
			if (!$this->validCSRFToken()) $this->error("Invalid Request");

			// Require administrator privileges for session management
			$this->requirePrivilege('manage sessions');

			// Get parameters
			$parameters = [];
			if (!empty($_REQUEST['last_hit_date'])) {
				$parameters['last_hit_date'] = $_REQUEST['last_hit_date'];
			}
			if (!empty($_REQUEST['max_records']) && is_numeric($_REQUEST['max_records'])) {
				$parameters['max_records'] = intval($_REQUEST['max_records']);
			}
			if (!empty($_REQUEST['user_login'])) {
				$parameters['user_login'] = $_REQUEST['user_login'];
			}

			// Execute purge
			$sessionList = new \Site\SessionList();
			$result = $sessionList->purgeExpired($parameters);

			if (!$result['success']) {
				$this->error($result['error'] ?? 'Failed to purge sessions');
				return;
			}

			$response = new \APIResponse();
			$response->addElement('success', 1);
			$response->addElement('count', $result['count']);
			$response->print();
		}

		public function getSiteAuditEvents() {
			$auditList = new \Site\AuditLog\EventList();

			$className = $_REQUEST['class'];
			$parameters = [
				'class_name'	=> $className
			];

			// See if class exists
			if (!class_exists($className)) $this->error("Class not found");

			$class = new $className;
			if ($class->get($_REQUEST['code'])) {
				$parameters['instance_id'] = $class->id;
			}

			$events = $auditList->find($parameters);
			if ($auditList->error()) $this->error($auditList->error());

			$response = new \APIResponse();
			$response->addElement('event',$events);
			$response->print();
		}

		/** @method sendSlackMessage
		 * Sends a message to a Slack channel
		 * @param string $channel The Slack channel to send the message to
		 * @param string $message The message to send
		 * @return bool Returns true on success, throws an exception on failure
		 */
		public function sendSlackMessage() {
			//print_r($_REQUEST);
			//exit;
			$channel = $_REQUEST["channel"];
			$message = $_REQUEST["message"];
			$sender = $_REQUEST["sender"] ?? "Web Portal";
			$client = new \Slack\Client();
			$result = $client->send($channel, $message, $sender);
			$response = new \APIResponse();
			if (!$result) {
				$response->error($client->error());
			}
			$response->print();
		}

		/** @method batchDeleteAuditEvents
		 * Batch delete audit events based on criteria
		 * @param string $class_name The class name of the audited objects
		 * @param int $count The maximum number of events to delete
		 * @return bool Returns true on success, throws an exception on failure
		 */
		public function batchDeleteAuditEvents() {
			$auditList = new \Site\AuditLog\EventList();

			$auditList->batchDeleteAuditEvents(
				$_REQUEST['class_name'],
				isset($_REQUEST['count']) ? intval($_REQUEST['count']) : 100
			);

			$response = new \APIResponse();
			if ($auditList->error()) {
				$response->error($auditList->error());
			}
			$response->print();
		}

		public function _methods() {
			return array(
				'findPages'	=> array(
					'description'	=> 'Find site pages matching criteria',
					'parameters'	=> array(
						'name'		=> array(
							'description'	=> 'Name of the page',
							'validation_method'	=> 'Site::Page::validName()',
						),
						'module'	=> array(
							'description'	=> 'Module of the page',
							'validation_method'	=> 'Site::Module::validCode()',
						),
						'searchString'	=> array(
							'description'	=> 'Search string for content matching',
							'validation_method'	=> 'Site::Page::validSearchString()',
						),
					)
				),
				'getPage'	=> array(
					'description'	=> 'Get specified site page',
					'path'			=> '/api/site/getPage/{module}/{view}/{index}',
					'return_element'	=> 'page',
					'return_type'	=> 'Site::Page',
					'parameters'	=> array(
						'module'	=> array(
							'description'	=> 'Module of the page',
							'required'	=> true,
							'validation_method'	=> 'Site::Module::validCode()',
						),
						'view'		=> array(
							'description'	=> 'View of the page',
							'required'	=> true,
							'validation_method'	=> 'Site::Page::validView()',
						),
						'index'		=> array(
							'description'	=> 'Index of the page',
							'validation_method'	=> 'Site::Page::validIndex()',
						),
					)
				),
				'addPage'	=> array(
					'description'	=> 'Add a new site page',
					'path'	=> '/api/site/addPage',
					'return_element'	=> 'page',
					'return_type'	=> 'Site::Page',
					'token_required'	=> true,
					'privilege_required'	=> 'edit site pages',
					'parameters'	=> array(
						'module'	=> array(
							'required' => true,
							'validation_method'	=> 'Site::Module::validCode()',
						),
						'view'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Page::validView()',
						),
						'index'		=> array(
							'validation_method'	=> 'Site::Page::validIndex()',
						),
					)
				),
				'deletePage'	=> array(
					'description'	=> 'Delete a site page',
					'token_required'	=> true,
					'privilege_required'	=> 'edit site pages',
					'parameters'	=> array(
						'module'	=> array(
							'required' => true,
							'validation_method'	=> 'Site::Module::validCode()',
						),
						'view'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Page::validView()',
						),
						'index'		=> array(
							'validation_method'	=> 'Site::Page::validIndex()',
						),
					)
				),
				'getPageMetadata'	=> array(
					'description'	=> 'Get metadata for a site page',
					'parameters'	=> array(
						'module'	=> array(
							'required' => true,
							'validation_method'	=> 'Site::Module::validCode()',
						),
						'view'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Page::validView()',
						),
						'index'		=> array(
							'validation_method'	=> 'Site::Page::validIndex()',
						),
						'key'		=> array(
							'validation_method'	=> 'Site::Page::validMetadataKey()',
						),
					)
				),
				'findPageMetadata'	=> array(
					'description'	=> 'Find metadata for site pages',
					'parameters'	=> array(
						'id'	=> array(
							'content-type'	=> 'int',
						),
						'module'	=> array(
							'validation_method'	=> 'Site::Module::validCode()',
						),
						'view'		=> array(
							'validation_method'	=> 'Site::Page::validView()',
						),
						'index'		=> array(
							'validation_method'	=> 'Site::Page::validIndex()',
						),
						'key'		=> array(
							'validation_method'	=> 'Site::Page::Metadata::validKey()',
						),
					),
				),
				'setPageMetadata'	=> array(
					'description'	=> 'Set metadata for a site page',
					'token_required'	=> true,
					'privilege_required'	=> 'edit site pages',
					'parameters'	=> array(
						'module'	=> array(
							'required' => true,
							'validation_method'	=> 'Site::Module::validCode()',
						),
						'view'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Page::validView()',
						),
						'index'		=> array(
							'validation_method'	=> 'Site::Page::validIndex()',
						),
						'key'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Page::Metadata::validKey()',
						),
						'value'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Page::Metadata::validValue()',
						),
					)
				),
				'findNavigationMenus'	=> array(
					'description'	=> 'Find navigation menus',
					'parameters'	=> array(
						'id'		=> array(
							'content-type'	=> 'int',
						),
						'parent_id'	=> array(
							'content-type'	=> 'int',
						),
						'code'		=> array(
							'validation_method'	=> 'Site::Navigation::Menu::validCode()',
						),
					)
				),
				'getNavigationMenu'	=> array(
					'description'	=> 'Get navigation menu',
					'url'	=> '/api/site/getNavigationMenu',
					'return_element'	=> 'menu',
					'return_type'	=> 'Site::Navigation::Menu',
					'parameters'	=> array(
						'code'		=> array(
							'description'	=> 'Code of the menu',
							'required' => true,
							'validation_method'	=> 'Site::Navigation::Menu::validCode()',
						)
					)
				),
				'addNavigationMenu'	=> array(
					'description'	=> 'Add navigation menu',
					'token_required'	=> true,
					'privilege_required'	=> 'edit site navigation',
					'return_element'	=> 'menu',
					'return_type'	=> 'Site::Navigation::Menu',
					'parameters'	=> array(
						'code'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Navigation::Menu::validCode()',
						),
						'title'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Navigation::Menu::validTitle()',
						),
						'parent_id'	=> array(
							'content-type'	=> 'int',
						),
						'target'	=> array(
							'validation_method'	=> 'Site::Navigation::Menu::validTarget()',
						),
					)
				),
				'findNavigationItems'	=> array(
					'description'	=> 'Find navigation items',
					'return_element'	=> 'item',
					'return_type'	=> 'Site::Navigation::Item',
					'parameters'	=> array(
						'id'		=> array(
							'content-type'	=> 'int',
						),
						'menu_code'		=> array(
							'validation_method'	=> 'Site::Navigation::Item::validMenuCode()',
						),
						'parent_id'		=> array(
							'content-type'	=> 'int',
						),
						'target'		=> array(
							'validation_method'	=> 'Site::Navigation::Item::validTarget()',
						),
					)
				),
				'addNavigationItem'	=> array(
					'description'	=> 'Add navigation item',
					'token_required'	=> true,
					'privilege_required'	=> 'edit site navigation',
					'return_element'	=> 'item',
					'return_type'	=> 'Site::Navigation::Item',
					'parameters'	=> array(
						'menu_code'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Navigation::Item::validMenuCode()',
						),
						'title'			=> array(
							'required' => true,
							'validation_method'	=> 'Site::Navigation::Item::validTitle()',
						),
						'parent_code'	=> array(
							'validation_method'	=> 'Site::Navigation::Item::validCode()',
						),
						'target'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Navigation::Item::validTarget()',
						),
						'alt'			=> array(
							'validation_method'	=> 'Site::Navigation::Item::safeString()',
						),
						'description'	=> array(
							'validation_method'	=> 'Site::Navigation::Item::safeString()',
						),
						'view_order'	=> array(
							'content-type' => 'int'
						),
					)
				),
				'getNavigationItem'	=> array(
					'description'	=> 'Get navigation item',
					'return_element'	=> 'item',
					'return_type'	=> 'Site::Navigation::Item',
					'parameters'	=> array(
						'menu_code'			=> array(
							'required' => true,
							'validation_method'	=> 'Site::Navigation::Menu::validCode()',
						),
						'title'				=> array(
							'required' => true,
							'validation_method'	=> 'Site::Navigation::Item::validTitle()',
						),
						'parent_code'		=> array(
							'validation_method'	=> 'Site::Navigation::Item::validCode()',
						),
					)
				),
				'updateNavigationItem'	=> array(
					'description'	=> 'Update navigation item',
					'token_required'	=> true,
					'privilege_required'	=> 'edit site navigation',
					'return_element'	=> 'item',
					'return_type'	=> 'Site::Navigation::Item',
					'parameters'	=> array(
						'id'			=> array(
							'required' => true,
							'content-type'	=> 'int',
						),
						'menu_code'		=> array(
							'validation_method'	=> 'Site::Navigation::Item::validMenuCode()',
						),
						'title'			=> array(
							'validation_method'	=> 'Site::Navigation::Item::validTitle()',
						),
						'target'		=> array(
							'validation_method'	=> 'Site::Navigation::Item::validTarget()',
						),
						'alt'			=> array(
							'validation_method'	=> 'Site::Navigation::Item::safeString()',
						),
						'description'	=> array(
							'validation_method'	=> 'Site::Navigation::Item::safeString()',
						),
						'view_order'	=> array(
							'content-type' => 'int'
						),
					)
				),
				'setConfiguration'	=> array(
					'description'	=> 'Set site configuration',
					'token_required'	=> true,
					'privilege_required'	=> 'configure site',
					'parameters'	=> array(
						'key'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Configuration::validKey()',
						),
						'value'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Configuration::validValue()',
						),
					)
				),
				'getConfiguration'	=> array(
					'description'	=> 'Get site configuration',
					'privilege_required'	=> 'configure site',
					'parameters'	=> array(
						'key'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Configuration::validKey()',
						),
					)
				),
				'deleteConfiguration'	=> array(
					'description'	=> 'Delete site configuration',
					'token_required'	=> true,
					'privilege_required'	=> 'configure site',
					'parameters'	=> array(
						'key'		=> array(
							'required' => true,
							'validation_method'	=> 'Site::Configuration::validKey()',
						),
					)
				),
				'addSiteMessage'	=> array(
					'description'	=> 'Add in-site message',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> array(
						'user_created'	=> array(
							'content-type'	=> 'int',
						),
						'date_created'	=> array(
							'validation_method'	=> 'Porkchop::validDate()',
						),
						'important'		=> array(
							'content-type'	=> 'bool',
						),
						'content'		=> array(
							'validation_method'	=> 'Site::Message::validContent()',
						),
						'parent_id'		=> array(
							'content-type'	=> 'int',
						),
					),
				),   
				'editSiteMessage'	=> array(
					'description'	=> 'Edit in-site message',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> array(
						'id'			=> array(
							'content-type'	=> 'int',
						),
						'user_created'	=> array(
							'content-type'	=> 'int',
						),
						'date_created'	=> array(
							'validation_method'	=> 'Porkchop::validDate()',
						),
						'important'		=> array(
							'content-type'	=> 'bool',
						),
						'content'		=> array(
							'validation_method'	=> 'Site::Message::validContent()',
						),
						'parent_id'		=> array(
							'content-type'	=> 'int',
						),
					),
				), 
				 'removeSiteMessage'	=> array(
					'description'	=> 'Remove in-site message',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> array(
						'id'	=> array(
							'content-type'	=> 'int',
						),
					),
				),
				'findSiteMessages'	=> array(
					'description'	=> 'Find site messages',
					'authentication_required'	=> true,
					'parameters'	=> [
						'send_user_id'		=> array(
							'content-type'	=> 'int',
						),
						'receive_user_id'	=> array(
							'content-type'	=> 'int',
						),
						'acknowledged'		=> array(
							'content-type'	=> 'bool',
						),
					]
				),
				'getSiteMessage'	=> array(
					'description'	=> 'Get site message',
					'authentication_required'	=> true,
					'path'	=> '/api/site/getSiteMessage/{id}',
					'return_element'	=> 'message',
					'return_type'	=> 'Site::SiteMessage',
					'parameters'	=> [
						'id'	=> array(
							'content-type'	=> 'int',
						),
					]
				),
				'addSiteMessageDelivery'	=> array(
					'description'	=> 'Add site message delivery',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> [
						'message_id'	=> array(
							'content-type'	=> 'int',
						),
						'user_id'	=> array(
							'content-type'	=> 'int',
						),
						'date_viewed'	=> array(
							'validation_method'	=> 'Porkchop::validDate()',
						),
						'date_acknowledged'	=> array(
							'validation_method'	=> 'Porkchop::validDate()',
						),
					]
				),   
				'editSiteMessageDelivery'	=> array(
					'description'	=> 'Edit site message delivery',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> [
						'id'	=> array(
							'content-type'	=> 'int',
						),
						'message_id'	=> array(
							'content-type'	=> 'int',
						),
						'user_id'	=> array(
							'content-type'	=> 'int',
						),
						'date_viewed'	=> array(
							'validation_method'	=> 'Porkchop::validDate()',
						),
						'date_acknowledged'	=> array(
							'validation_method'	=> 'Porkchop::validDate()',
						),
					]
				), 
				'getSiteMessageMetaDataListByItemId'	=> array(
					'description'	=> 'Get metadata for a site message',
					'parameters'	=> [
						'item_id'	=> array(
							'content-type'	=> 'int',
						),
					]
				),
				'removeSiteMessageDelivery'	=> array(
					'description'	=> 'Remove site message delivery',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> [
						'id'	=> array(
							'content-type'	=> 'int',
							'required'	=> true,
						),
					]
				),
				'findSiteMessageDeliveries'	=> array(
					'description'	=> 'Find site message deliveries',
					'authentication_required'	=> true,
					'parameters'	=> [
						'from'	=> array(
							'content-type'	=> 'int',
						),
						'to'	=> array(
							'content-type'	=> 'int',
						),
						'user_id'	=> array(
							'content-type'	=> 'int',
						),
						'message_id'	=> array(
							'content-type'	=> 'int',
						),
						'viewed'	=> array(
							'content-type'	=> 'bool',
						),
						'acknowledged'	=> array(
							'content-type'	=> 'bool',
						),
					]
				),
				'mySiteMessageCount' => array(
					'description'	=> 'Get count of site messages for current user',
					'authentication_required'	=> true,
				),
				'addSiteMessageMetaData'	=> array(
					'description'	=> 'Add metadata to a site message',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> [
						'item_id'	=> array(
							'required'	=> true,
							'content-type'	=> 'int',
						),
						'key'	=> array(
							'required'	=> true,
							'validation_method'	=> 'Site::Message::Metadata::validKey()',
						),
						'value'	=> array(
							'validation_method'	=> 'Site::Message::Metadata::validValue()',
						),
					]
				), 
				'editSiteMessageMetaData'	=> array(
					'description'	=> 'Edit metadata for a site message',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> [
						'item_id'	=> array(
							'required'	=> true,
							'content-type'	=> 'int',
						),
						'key'	=> array(
							'required'	=> true,
							'validation_method'	=> 'Site::Message::Metadata::validKey()',
						),
						'value'	=> array(
							'validation_method'	=> 'Site::Message::Metadata::validValue()',
						),
					]
				), 
				'removeSiteMessageMetaData'	=> array(
					'description'	=> 'Remove metadata from a site message',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> [
						'item_id' => array('required' => true)
					]
				),
				'acknowledgeSiteMessageByUserId'	=> array(
					'description'	=> 'Acknowledge all site messages for a user',
					'token_required'	=> true,
					'authentication_required'	=> true,
					'parameters'	=> [
						'user_created' => array('required' => true)
					]
				),
				'addTermsOfUse' => array(
					'description'	=> 'Add a new terms of use',
					'token_required'	=> true,
					'privilege_required'	=> 'manage terms of use',
					'parameters'	=> array(
						'code'	=> array(
							'validation_method'	=> 'Site::TermsOfUse::validCode()',
						),
						'name'	=> array(
							'required' => true,
							'validation_method'	=> 'Site::TermsOfUse::safeString()',
						),
						'description' => array(
							'validation_method'	=> 'Site::TermsOfUse::safeString()',
						)
					)
				),
				'addTermsOfUseVersion' => array(
					'description'	=> 'Add a new terms of use version',
					'token_required'	=> true,
					'privilege_required'	=> 'manage terms of use',
					'parameters'	=> array(
						'tou_code'	=> array('required' => true),
						'status'	=> array(
							'options'	=> array (
								'NEW','CANCELLED','PUBLISHED'
							)
						),
						'content'	=> array(
							'required' => true,
							'validation_method'	=> 'Site::TermsOfUse::safeString()',
						),
					),
				),
				'activateTermsOfUseVersion' => array(
					'description'	=> 'Activate a terms of use version',
					'token_required'	=> true,
					'privilege_required'	=> 'manage terms of use',
					'parameters'	=> array(
						'version_id'	=> array(
							'required' => true,
							'content-type'	=> 'int',
						),
					)
				),
				'cancelTermsOfUseVersion' => array(
					'description'	=> 'Cancel a terms of use version',
					'token_required'	=> true,
					'privilege_required'	=> 'manage terms of use',
					'parameters'	=> array(
						'version_id'	=> array(
							'required' => true,
							'content-type'	=> 'int',
						),
					)
				),
				'findTermsOfUse' => array(
					'description'	=> 'Find terms of use by name or code',
					'authentication_required'	=> true,
					'privilege_required'	=> 'manage terms of use',
					'parameters'	=> array(
						'name'	=> array(),
						'code'	=> array(),
					)
				),
				'timestamp' => array(
					'description'	=> 'Get current timestamp',
					'path'	=> '/api/site/timestamp',
					'return_element'	=> 'timestamp',
					'return_type'	=> 'int'
				),
				'search'	=> array(
					'string' => array('required' => true)
				),
				'getAllSiteCounters' => array(
					'description'	=> 'Get all site counters',
					'authentication_required'	=> true,
					'path'	=> '/api/site/getAllSiteCounters',
					'return_element'	=> 'counter',
					'return_type'	=> 'Site::Counter'
				),
				'setAllSiteCounters' => array(),
				'incrementSiteCounter' => array(
					'name'  => array('required' => true),
				),
				'getSiteCounter' => array(
					'description'	=> 'Get the value of a site counter',
					'authentication_required'	=> true,
					'path'	=> '/api/site/getSiteCounter/{name}',
					'return_element'	=> 'counter',
					'return_type'	=> 'Site::Counter',
					'parameters'	=> [
						'name'	=> array(
							'description'	=> 'Name of the counter',
							'required' => true,
							'validation_method'	=> 'Site::Counter::validName()',
						)
					]
				),
				'addSiteHeader' => array(
					'description'	=> 'Add a new site header',
					'authentication_required'	=> true,
					'privilege_required'	=> 'manage site headers',
					'path'	=> '/api/site/addSiteHeader',
					'return_element'	=> 'header',
					'return_type'	=> 'Site::Header',
					'parameters'	=> [
						'name'  => array(
							'description'	=> 'Name of the header',
							'required' => true,
							'validation_method'	=> 'Site::Header::validName()',
						),
						'value' => array(
							'description'	=> 'Value of the header',
							'required' => true,
							'validation_method'	=> 'Site::Header::validValue()',
						)
					]
				),
				'getSiteHeader' => array(
					'description'	=> 'Get the value of a site header',
					'authentication_required'	=> true,
					'path'	=> '/api/site/getSiteHeader/{name}',
					'return_element'	=> 'header',
					'return_type'	=> 'Site::Header',
					'parameters'	=> [
						'name'  => array(
							'description'	=> 'Name of the header',
							'required' => true,
							'validation_method'	=> 'Site::Header::validName()',
						)
					]
				),
				'updateSiteHeader' => array(
					'name'  => array('required' => true),
					'value' => array('required' => true)
				),
				'findSiteHeaders' => array(
					'name'  => array('required' => true),
					'value' => array('required' => true)
				),
				'getSiteStatus' => array(),
				'getNodeHealth' => array(),
				'getTOULatestVersion'	=> array(
					'description'	=> 'Get the latest published version of a terms of use',
					'parameters'	=> array(
						'tou_id'	=> array(
							'required' => true,
							'content-type'	=> 'int',
						),
					)
				),
				'getSiteAuditEvents'	=> array(
					'description'				=> 'Get events related to an object instance',
					'authentication_required'	=> true,
					'parameters'	=> [
						'class'	=> array('required'),
						'code'	=> array('required')
					]
				),
				'purgeExpiredSessions'	=> array(
					'description'				=> 'Purge expired sessions from the database',
					'token_required'			=> true,
					'authentication_required'	=> true,
					'privilege_required'		=> 'manage sessions',
					'path'						=> '/api/site/purgeExpiredSessions',
					'return_element'			=> 'result',
					'return_type'				=> 'array',
					'parameters'				=> [
						'last_hit_date'	=> array(
							'description'	=> 'Delete only records with last_hit_date before this date (defaults to 6 months ago, never less than 48 hours)',
							'content-type'	=> 'datetime',
						),
						'max_records'	=> array(
							'description'	=> 'Delete no more than this number of records',
							'content-type'	=> 'int',
						),
						'user_login'	=> array(
							'description'	=> 'Delete only records for the specified account',
							'content-type'	=> 'string',
						)
					]
				),
				'sendSlackMessage' => array(
					'description'	=> 'Send a message to a Slack channel',
					'token_required'	=> false,
					'privilege_required'	=> 'send slack messages',
					'parameters'	=> array(
						'channel' => array(
							'required' => true,
							'validation_method' => 'Slack::Client::validChannel()',
						),
						'message' => array(
							'required' => true,
							'validation_method' => 'Slack::Client::validMessage()',
						),
						'sender' => array(
							'required' => false,
							'validation_method' => 'Slack::Client::validSender()',
						),
					)
				),
				'batchDeleteAuditEvents' => array(
					'description'	=> 'Batch delete audit log events',
					'token_required'	=> true,
					'privilege_required'	=> 'manage audit logs',
					'parameters'	=> array(
						'class_name' => array(
							'required' => true,
							'validation_method'	=> 'Porkchop::validClassName()',
						),
						'count' => array(
							'required' => true,
							'content-type'	=> 'integer',
						),
					)
				)
			);		
		}
	}
