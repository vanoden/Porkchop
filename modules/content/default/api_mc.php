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
	### Echo some specified value					###
	###################################################
	function parse() {
		print $GLOBALS['_page']->parse($_REQUEST['string']);
	}
	###################################################
	### Get Details regarding Specified Product		###
	###################################################
	function findMessages() {
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
		if ($message_list->error) error($message_list->error);
		else{
			$response->message = $messages;
			$response->success = 1;
		}

		api_log('content',$_REQUEST,$response);

		# Send Response
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	###################################################
	### Get Details regarding Specified Message		###
	###################################################
	function getMessage() {
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
		if ($message->error) error($message->error);
		else{
			$response->request = $_REQUEST;
			$response->message = $message;
			$response->success = 1;
		}

		api_log('content',$_REQUEST,$response);

		# Send Response
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	###################################################
	### Update Specified Message					###
	###################################################
	function updateMessage() {
		# Default StyleSheet
		if (! isset($_REQUEST["stylesheet"])) $_REQUEST["stylesheet"] = 'content.message.xsl';
		$response = new \HTTP\Response();

		# Initiate Product Object
		$message = new \Content\Message($_REQUEST['id']);
		if (! $message->id) error("Message '".$_REQUEST['id']."' not found");

		$parameters = array();
		if (isset($_REQUEST['name'])) $parameters['name'] = $_REQUEST['name'];
		if (isset($_REQUEST['title'])) $parameters['title'] = $_REQUEST['title'];
		if (isset($_REQUEST['content'])) $parameters['content'] = $_REQUEST['content'];

		# Find Matching Threads
		$message->update($parameters);

		# Error Handling
		if ($message->error) error($message->error);
		else{
			$response->message = $message;
			$response->success = 1;
		}

		api_log('content',$_REQUEST,$response);
		# Send Response
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
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
		print formatOutput($response); #,array("stylesheet" => $_REQUEST["stylesheet"]));
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

    function formatOutput($object) {
        if ($_REQUEST['_format'] == 'json') {
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

?>
