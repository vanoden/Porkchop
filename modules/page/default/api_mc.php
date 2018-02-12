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
		header('Content-Type: application/xml');
		print XMLout($response);
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		if (isset($_REQUEST['module'])) $page->module = $_REQUEST['module'];
		elseif (isset($_REQUEST['target'])) {
			$page->module = 'content';
			$page->view = 'index';
			$page->index = $_REQUEST['target'];
		}
		if (isset($_REQUEST['view'])) $page->view = $_REQUEST['view'];
		if (isset($_REQUEST['index'])) $page->index = $_REQUEST['index'];

		# Find Matching Page
		$page->get();

		# Error Handling
		if ($page->error) error($page->error);
		else{
			$response->request = $_REQUEST;
			$response->page = $page;
			$response->success = 1;
		}

		api_log('content',$_REQUEST,$response);

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	###################################################
	### Get Details regarding Specified Product		###
	###################################################
	function addMessage() {
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	###################################################
	### Update Specified Message					###
	###################################################
	function updateMessage() {
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	###################################################
	### Purge Cache of Specified Message			###
	###################################################
	function purgeMessage() {
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	###################################################
	### Add Page Metadata							###
	###################################################
	function addMetadata() {
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	###################################################
	### Update Page Metadata						###
	###################################################
	function updateMetadata() {
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	###################################################
	### Get Details regarding Specified Product		###
	###################################################
	function findNavigationItems()
	{
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
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}


	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message)
	{
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response = new \HTTP\Response();
		$response->message = $message;
		$response->success = 0;
		api_log('content',$_REQUEST,$response);
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options = array()) {
		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_SERIALIZER_OPTION_INDENT        => '    ',
    	    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
			'rootName'							=> 'opt',
    	);
    	$xml = &new XML_Serializer($options);
	   	if ($xml->serialize($object)) {
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if (isset($user_options["stylesheet"])) {
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
	
	function confirm_customer() {
		if (! in_array('content reporter',$GLOBALS['_SESSION_']->customer->roles)) {
			$this->error = "You do not have permissions for this task.";
			return 0;
		}
	}

?>
