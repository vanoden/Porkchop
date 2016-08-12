<?
	###############################################
    ### Handle API Request for Media Info and	###
    ### Management								###
    ### A. Caravello 10/4/2014					###
    ###############################################
	# Load Module Classes
	require_once(MODULES."/media/_classes/default.php");

	# Call Requested Event
	if ($_REQUEST["method"])
	{
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}

	###################################################
	### Just See if Server Is Communicating			###
	###################################################
	function ping()
	{
		$response->header->session = $GLOBALS['_SESSION_']->code;
		$response->header->method = $_REQUEST["method"];
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}
	
	###################################################
	### Add Media Item								###
	###################################################
	function addMediaItem()
	{
		# Make Sure Upload was Successful
		check_upload($_FILES['file']);

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';
		
		# Initiate Media Item Object
		$_item = new MediaItem();
		if ($_item->error) error($_item->error);

		# Add Document to Database
		$item = $_item->add(
			array (
				'name'	=> $_REQUEST['name'],
				'type'	=> $_REQUEST['type'],
				'code'	=> $_REQUEST['code']
			)
		);
		if ($_item->error) error($_item->error);
		$_file = new MediaFile();
		$_file->save(
			$item->id,
			$_REQUEST['index'],
			$_FILES['file']['tmp_name'],
			$_FILES['file']['name'],
			$_FILES['file']['type'],
			$_FILES['file']['size']
		);
		if ($_file->error) error($_file->error);
		$response->item = $item;
		$response->success = 1;

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}

	###################################################
	### Find Media Items							###
	###################################################
	function findMediaItems()
	{
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.items.xsl';

		# Initiate Media Item Object
		$_item = new MediaItem();
		if ($_item->error) error($_item->error);

		# Get Items from Database
		$parameters = array();
		if ($_REQUEST['type']) $parameters['type'] = $_REQUEST['type'];
		foreach ($_REQUEST['key'] as $key)
		{
			$parameters[$key] = $_REQUEST['value'][$key];
		}
		$items = $_item->find($parameters);

		# Error Handling
		if ($_item->error) error($_item->error);
		else{
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
	function getMediaItem()
	{
		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';

		# Initiate Media Item Object
		$_item = new MediaItem();
		if ($_item->error) error($_item->error);

		# Get Item from Database
		$item = $_item->get($_REQUEST['code']);

		# Error Handling
		if ($_item->error) error($_item->error);
		else{
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
	function downloadMediaFile()
	{
		# Initiate Media File Object
		$_file = new MediaFile();
		if ($_file->error) error($_file->error);

		# Get File from Repository
		$file = $_file->load($_REQUEST['code']);

		# Error Handling
		if ($_file->error) error($_file->error);
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
	function downloadMediaImage()
	{
		# Initiate Media Image Object
		$_image = new MediaImage();
		$image = $_image->get($_REQUEST['code']);
		if ($_image->error) app_error("Error getting MediaImage: ".$_image->error,'error',__FILE__,__LINE__);
		if (! $image->id) error("Image not found");
	
		# Get Associated File
		$_file = new MediaFile();
		list($file) = $_file->find(array("item_id",$image->id));
		if ($_file->error) error($_file->error);

		# Get File from Repository
		$file = $_file->load($_REQUEST['code']);

		# Error Handling
		if ($_file->error) error($_file->error);

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
	function setMediaMetadata()
	{
		# Make Sure Upload was Successful
		check_upload($_FILES['file']);

		# Default StyleSheet
		if (! $_REQUEST["stylesheet"]) $_REQUEST["stylesheet"] = 'media.item.xsl';
		
		# Initiate Media Item Object
		$_item = new MediaItem();
		if ($_item->error) error($_item->error);

		# Find Item
		$item = $_item->get($_REQUEST['code']);
		if ($_item->error) error("Error finding item: ".$_item->error);
		if (! $item->id) error("Item not found");

		# Add Meta Tag
		$_item->setMeta(
			$item->id,$_REQUEST['label'],$_REQUEST['value']
		);
		if ($_item->error) error($_item->error);
		$response->success = 1;

		# Send Response
		header('Content-Type: application/xml');
		print XMLout($response); #,array("stylesheet" => $_REQUEST["stylesheet"])
	}

	function check_upload($request)
	{
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
	###################################################
	### Application Error							###
	###################################################
	function app_error($message,$file = __FILE__,$line = __LINE__)
	{
		app_log($message,'error',$file,$line);
		error('Application Error');
	}
	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message)
	{
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
	function XMLout($object,$user_options = '')
	{
		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_SERIALIZER_OPTION_INDENT        => '    ',
    	    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
    	);
		if ($user_options["rootname"])
		{
			$options["rootName"] = $user_options["rootname"];
		}
    	$xml = &new XML_Serializer($options);
	   	if ($xml->serialize($object))
		{
			//error_log("Returning ".$xml->getSerializedData());
			$output = $xml->getSerializedData();
			if ($user_options["stylesheet"])
			{
				$output = "<?xml-stylesheet type=\"text/xsl\" href=\"/".$user_options["stylesheet"]."\"?>".$output;
			}
			return $output;
		}
	}
?>
