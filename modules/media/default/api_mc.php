<?
	###############################################
    ### Handle API Request for Media Info and	###
    ### Management								###
    ### A. Caravello 10/4/2014					###
    ###############################################

	# Call Requested Event
	if ($_REQUEST["method"]) {
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping() {
		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	
	###################################################
	### Add Media Item								###
	###################################################
	function addMediaItem() {
		# Make Sure Upload was Successful
		check_upload($_FILES['file']);

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';
		
		# Initiate Media Item Object
		$item = new \Media\Item();
		if ($item->error) error($item->error);

		# Add Document to Database
		$item->add(
			array (
				'name'	=> $_REQUEST['name'],
				'type'	=> $_REQUEST['type'],
				'code'	=> $_REQUEST['code']
			)
		);
		if ($item->error) error($item->error);
		$file = new \Media\File();
		$file->item_id = $item->id;
		$file->index = $_REQUEST['index'];
		$file->original_file = $_FILES['file']['name'];
		$file->mime_type = $_FILES['file']['type'];
		$file->size = $_FILES['file']['size'];
	
		$file->save($_FILES['file']['tmp_name']);
		if ($file->error) error($file->error);

		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->item = $item;
		$response->success = 1;

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}

	###################################################
	### Find Media Items							###
	###################################################
	function findMediaItems() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.items.xsl';

		# Initiate Media Item Object
		$itemlist = new \Media\ItemList();
		if ($itemlist->error) error($itemlist->error);

		# Get Items from Database
		$parameters = array();
		if ($_REQUEST['type']) $parameters['type'] = $_REQUEST['type'];
		foreach ($_REQUEST['key'] as $key) {
			$parameters[$key] = $_REQUEST['value'][$key];
		}
		$items = $itemlist->find($parameters);

		# Error Handling
		if ($itemlist->error) error($itemlist->error);
		else{
			$response = new \HTTP\Response();
			$response->header->session = $GLOBALS['_SESSION_']->code;
			$response->header->method = $_REQUEST["method"];
			$response->header->date = system_time();
			$response->items = $items;
			$response->success = 1;
		}

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}

	###################################################
	### Get Media Item								###
	###################################################
	function getMediaItem() {
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';

		# Initiate Media Item Object
		$_item = new \Media\Item();
		if ($item->error) error($item->error);

		# Get Item from Database
		$item->get($_REQUEST['code']);

		# Error Handling
		if ($item->error) error($item->error);
		else{
			$response = new \HTTP\Response();
			$response->header->session = $GLOBALS['_SESSION_']->code;
			$response->header->method = $_REQUEST["method"];
			$response->header->date = system_time();
			$response->item = $item;
			$response->success = 1;
		}

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}
	
	###################################################
	### Download File								###
	###################################################
	function downloadMediaFile() {
		# Initiate Media File Object
		$file = new \Media\File();
		if ($file->error) error($file->error);

		# Get File from Repository
		$file->load($_REQUEST['code']);

		# Error Handling
		if ($file->error) error($file->error);
		else{
			app_log("Downloading ".$file->code.", type ".$file->mime_type.", ".$file->size." bytes.",'debug',__FILE__,__LINE__);
			if ($file->size != strlen($file->content)) app_log("Size doesn't match: ".$file->size." != ".strlen($file->content),'notice',__FILE__,__LINE__);
			header('Content-Type: '.$file->mime_type);
			header('Content-Disposition: '.$file->disposition.';filename='.$file->original_file);
			print ($file->content);
			exit;
		}
	}
	
	###################################################
	### downloadMediaImage							###
	###################################################
	function downloadMediaImage() {
		# Initiate Media Image Object
		$image = new \Media\Image();
		$image->get($_REQUEST['code']);
		if ($image->error) app_error("Error getting MediaImage: ".$image->error,'error',__FILE__,__LINE__);
		if (! $image->id) error("Image not found");
	
		# Get Associated File
		$filelist = new \Media\FileList();
		list($file) = $filelist->find(array("item_id",$image->id));
		if ($file->error) error($file->error);

		# Get File from Repository
		$file->load($_REQUEST['code']);

		# Error Handling
		if ($file->error) error($file->error);

		app_log("Generating thumbnail for image '".$file->code."', type ".$file->mime_type,'debug',__FILE__,__LINE__);
		if ($file->size != strlen($file->content)) error("Size doesn't match: ".$file->size." != ".strlen($file->content));
		header('Content-Type: '.$file->mime_type);
		header('Content-Disposition: '.$file->disposition.';filename='.$file->original_file);

		# Resizing
			

		print ($file->content);
		exit;
	}
	
	###################################################
	### Add Media Metadata							###
	###################################################
	function setMediaMetadata() {
		# Make Sure Upload was Successful
		check_upload($_FILES['file']);

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';
		
		# Initiate Media Item Object
		$item = new \Media\Item();
		if ($item->error) error($item->error);

		# Find Item
		$$item->get($_REQUEST['code']);
		if ($_item->error) error("Error finding item: ".$_item->error);
		if (! $item->id) error("Item not found");

		# Add Meta Tag
		$item->setMeta(
			$item->id,$_REQUEST['label'],$_REQUEST['value']
		);
		if ($item->error) error($item->error);

		$response = new \HTTP\Response();
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->header->date = system_time();
		$response->success = 1;

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}

	function check_upload($request) {
		try {
			// Undefined | Multiple Files | $_FILES Corruption Attack
			// If this request falls under any of them, treat it invalid.
			if (
				!isset($request['error']) ||
				is_array($request['error'])
			) {
				throw new RuntimeException('Invalid parameters.');
			}
		
			// Check $_FILES['upfile']['error'] value.
			switch ($request['error']) {
				case UPLOAD_ERR_OK:
					break;
				case UPLOAD_ERR_NO_FILE:
					throw new RuntimeException('No file sent.');
				case UPLOAD_ERR_INI_SIZE:
				case UPLOAD_ERR_FORM_SIZE:
					throw new RuntimeException('Exceeded filesize limit.');
				default:
					throw new RuntimeException('Unknown errors.');
			}
		
			// You should also check filesize here. 
			if ($request['size'] > 32000000) {
				throw new RuntimeException('Exceeded filesize limit.');
			}
		} catch (RuntimeException $e) {
			error("Problem with file upload: ".$e->getMessage());
			return 0;
		}
		return 1;
	}
	function schemaVersion() {
		$schema = new \Media\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->version();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	function schemaUpgrade() {
		$schema = new \Media\Schema();
		if ($schema->error) {
			app_error("Error getting version: ".$schema->error,__FILE__,__LINE__);
		}
		$version = $schema->upgrade();
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = $version;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__) {
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message) {
		$_REQUEST["stylesheet"] = '';
		error_log($message);
		$response->message = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}

	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options = '') {
		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_SERIALIZER_OPTION_INDENT        => '    ',
    	    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
			'rootName'							=> 'opt'
    	);
    	$xml = &new XML_Serializer($options);
	   	if ($xml->serialize($object)) {
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if ($user_options["stylesheet"]) {
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
?>
