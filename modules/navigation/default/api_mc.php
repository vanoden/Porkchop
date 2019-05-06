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
	### Query Menu List								###
	###################################################
	function findMenus() {
		# Default StyleSheet
		if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'navigation.menu.xsl';
		$response = new \HTTP\Response();

		# Initiate Page List
		$page_list = new \Navigation\MenuList();

		# Find Matching Threads
		$parameters = array();
		$pages = $menu_list->find($parameters);

		# Error Handling
		if ($menu_list->error) error($menu_list->error);
		else{
			$response->menu = $menus;
			$response->success = 1;
		}

		api_log('content',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}
	###################################################
	### Get Details regarding Specified Page		###
	###################################################
	function getMenuButtons() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.button.xsl';
		$response = new \HTTP\Response();

		# Initiate Page Object
		$menu = new \Navigation\Menu();
		if (isset($_REQUEST['code'])) $menu->get($_REQUEST['code']);

		# Error Handling
		if ($menu->error) error($menu->error);
		elseif ($menu->id) {
			$response->request = $_REQUEST;
			$response->button = $button;
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
	###################################################
	### Get Details regarding Specified Product		###
	###################################################
	function findNavigationItems() {
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
		if ($_menu->error) error($_menu->error);
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
		$schema = new \Navigation\Schema();
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
		$schema = new \Navigation\Schema();
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
