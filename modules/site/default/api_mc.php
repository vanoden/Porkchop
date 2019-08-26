<?php
    ###############################################
    ### Handle API Request for Content Info and	###
    ### Management								###
    ### A. Caravello 5/7/2009               	###
    ###############################################

	# Call Requested Event
	#error_log($_REQUEST['method']." Request received");
	#error_log(print_r($_REQUEST,true));
	if ($_REQUEST["method"])
	{
		app_log("Method ".$_REQUEST["method"]." called with ".$GLOBALS['_REQUEST_']->query_vars);
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('content operator')) {
		header("location: /_content/home");
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->message = "PING RESPONSE";
		$response->success = 1;
		api_log('content',$_REQUEST,$response);
		print formatOutput($response);
	}
	###################################################
	### Query Page List								###
	###################################################
	function findPages() {
		# Default StyleSheet
		if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'content.message.xsl';
		$response = new \HTTP\Response();

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
			$response->page = $pages;
			$response->success = 1;
		}

		api_log('content',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Get Details regarding Specified Page		###
	###################################################
	function getPage() {
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
		print formatOutput($response);
	}
	function addPage() {
		if (! $GLOBALS['_SESSION_']->customer->can('change content pages')) error("Permission Denied");

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
		print formatOutput($response);
	}
	###################################################
	### Get Details regarding Specified Product		###
	###################################################
	function addMessage() {
		if (! $GLOBALS['_SESSION_']->customer->can('change content messages')) error("Permission Denied");

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
		$response = new \HTTP\Response();

		# Initiate Product Object
		$_content = new Content();

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
		print formatOutput($response);
	}
	###################################################
	### Update Specified Message					###
	###################################################
	function updateMessage() {
		if (! $GLOBALS['_SESSION_']->customer->can('change content messages')) error("Permission Denied");

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
		$response = new \HTTP\Response();

		# Initiate Product Object
		$content = new Content($_REQUEST['id']);
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
		print formatOutput($response);
	}
	###################################################
	### Purge Cache of Specified Message			###
	###################################################
	function purgeMessage() {
		if (! $GLOBALS['_SESSION_']->customer->can('change content messages')) error("Permission Denied");

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'content.message.xsl';
		$response = new \HTTP\Response();

		# Initiate Product Object
		$_content = new Content();

		# Get Message
		$message = $_content->get($_REQUEST['target']);
		if ($_content->error)
		{
			app_error($_content->error,__FILE__,__LINE__);
			error("Application error");
		}
		if (! $message->id)
			error("Unable to find matching message");

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
		print formatOutput($response);
	}

	###################################################
	### Get Metadata for current view				###
	###################################################
	function getMetadata() {
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
		if ($_metadata->error) error($_metadata->error);
		else{
			$response->metadata = $metadata;
			$response->success = 1;
		}

		# Send Response
		api_log('content',$_REQUEST,$response);
		print formatOutput($response);
	}
	###################################################
	### Get Metadata for current view				###
	###################################################
	function findMetadata() {
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
		if ($_metadata->error) error($_metadata->error);
		else{
			$response->metadata = $metadata;
			$response->success = 1;
		}

		# Send Response
		api_log('content',$_REQUEST,$response);
		print formatOutput($response);
	}
	###################################################
	### Add Page Metadata							###
	###################################################
	function addMetadata() {
		if (! $GLOBALS['_SESSION_']->customer->can('change content metadata')) error("Permission Denied");

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
		if ($_metadata->error) error($_metadata->error);
		else{
			$response->metadata = $metadata;
			$response->success = 1;
		}

		# Send Response
		api_log('content',$_REQUEST,$response);
		print formatOutput($response);
	}
	###################################################
	### Update Page Metadata						###
	###################################################
	function updateMetadata() {
		if (! $GLOBALS['_SESSION_']->customer->can('change content metadata')) error("Permission Denied");

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
		else
		{
			error("Could not find matching object");
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
		print formatOutput($response);
	}
	function setPageMetadata() {
		if (! $GLOBALS['_SESSION_']->customer->can('change content metadata')) error("Permission Denied");

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
		print formatOutput($response);
	}
	###################################################
	### Get List of Site Navigation Menus			###
	###################################################
	function findNavigationMenus() {
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
		print formatOutput($response);
	}
	###################################################
	### Get Items from a Site Navigation Menu		###
	###################################################
	function findNavigationItems() {
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
		print formatOutput($response);
	}

	###################################################
	### Manage Page Schema							###
	###################################################
	function schemaVersion() {
		$schema = new \Page\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}
	function schemaUpgrade() {
		$schema = new \Page\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		print formatOutput($response);
	}

	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response = new \HTTP\Response();
		$response->message = $message;
		$response->success = 0;
		api_log('content',$_REQUEST,$response);
		print formatOutput($response);
		exit;
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function formatOutput($object) {
		if (isset($_REQUEST['_format']) && $_REQUEST['_format'] == 'json') {
			$format = 'json';
			header('Content-Type: application/json');
		}
		else {
			$format = 'xml';
			header('Content-Type: application/xml');
		}
		$document = new \Document($format);
		$document->prepare($object);
		return $document->content();
	}

	function confirm_customer() {
		if (! in_array('content reporter',$GLOBALS['_SESSION_']->customer->roles)) {
			$this->error = "You do not have permissions for this task.";
			return 0;
		}
	}

?>
