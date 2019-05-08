<?php
    ###############################################
    ### Handle API Request for Navigation and	###
    ### Management								###
    ### A. Caravello 5/7/2009               	###
    ###############################################

	$page = new \Site\Page();

	# Call Requested Event
	if ($_REQUEST["method"]) {
		app_log("Method ".$_REQUEST["method"]." called with ".$GLOBALS['_REQUEST_']->query_vars);
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	else $page->requireRole('content operator');

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
		$menu_list = new \Navigation\MenuList();

		# Find Matching Threads
		$parameters = array();
		$menus = $menu_list->find($parameters);

		# Error Handling
		if ($menu_list->error) error($menu_list->error);
		else{
			$response->menu = $menus;
			$response->count = $menu_list->count();
			$response->success = 1;
		}

		api_log('content',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Get Menu									###
	###################################################
	function getMenu() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.menu.xsl';
		$response = new \HTTP\Response();

		$parameters = array();

		if (isset($_REQUEST['code'])) {
			$menu = new \Navigation\Menu();
			if ($menu->get($_REQUEST['code'])) {
				$response->request = $_REQUEST;
				$response->menu = $menu;
				$response->success = 1;
			}
			elseif ($menu->error()) {
				error($menu->error());
			}
			else {
				error("Menu not found");
			}
		}
		else error("menu code required");

		api_log('navigation',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Add Menu									###
	###################################################
	function addMenu() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.menu.xsl';
		$response = new \HTTP\Response();

		$parameters = array();

		if (! isset($_REQUEST['code'])) error("code required");
		$parameters['code'] = $_REQUEST['code'];
		$parameters['title'] = $_REQUEST['title'];

		$menu = new \Navigation\Menu();
		if ($menu->add($parameters)) {
			$response->menu = $menu;
			$response->success = 1;
		}
		elseif ($menu->error()) {
			error($menu->error());
		}
		else {
			error("Menu not found");
		}

		api_log('navigation',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Get Menu Items								###
	###################################################
	function findItems() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.item.xsl';
		$response = new \HTTP\Response();

		$parameters = array();
		$itemlist = new \Navigation\ItemList();

		if (isset($_REQUEST['menu_code'])) {
			$menu = new \Navigation\Menu();
			if ($menu->get($_REQUEST['menu_code'])) {
				$parameters['menu_id'] = $menu->id;
			}
			else {
				error("Menu '".$_REQUEST['menu_code']."' not found");
			}
		}

		$items = $itemlist->find($parameters);
		if ($itemlist->error()) error($itemlist->error());
		else {
			$response->request = $_REQUEST;
			$response->item = $items;
			$response->count = $itemlist->count();
			$response->success = 1;
		}

		api_log('navigation',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Add Menu Item								###
	###################################################
	function addItem() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.item.xsl';
		$response = new \HTTP\Response();

		$parameters = array();

		if (! isset($_REQUEST['menu_code'])) error("menu_code required");
		$menu = new \Navigation\Menu();
		if (! $menu->get($_REQUEST['menu_code'])) error("Menu not found");

		$parameters['menu_id'] = $menu->id;	
		$parameters['title'] = $_REQUEST['title'];
		$parameters['target'] = $_REQUEST['target'];
		$parameters['alt'] = $_REQUEST['alt'];
		$parameters['description'] = $_REQUEST['description'];
		$parameters['view_order'] = $_REQUEST['view_order'];

		$item = new \Navigation\Item();
		if ($item->add($parameters)) {
			$response->item = $item;
			$response->success = 1;
		}
		elseif ($item->error()) {
			error($item->error());
		}

		api_log('navigation',$_REQUEST,$response);

		# Send Response
		print formatOutput($response);
	}

	###################################################
	### Update Menu Item							###
	###################################################
	function updateItem() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'navigation.item.xsl';
		$response = new \HTTP\Response();

		$parameters = array();

		if (! isset($_REQUEST['id'])) error("id required");
		$item = new \Navigation\Item($_REQUEST['id']);
		if ($item->error) error($item->error);
		if (! $item->id) error("Item not found");

		$parameters['title'] = $_REQUEST['title'];
		$parameters['target'] = $_REQUEST['target'];
		$parameters['alt'] = $_REQUEST['alt'];
		$parameters['description'] = $_REQUEST['description'];
		$parameters['view_order'] = $_REQUEST['view_order'];

		if ($item->update($parameters)) {
			$response->request = $_REQUEST;
			$response->item = $item;
			$response->success = 1;
		}
		elseif ($item->error()) {
			error($item->error());
		}

		api_log('navigation',$_REQUEST,$response);

		# Send Response
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
