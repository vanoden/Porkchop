<?php
    ###############################################
    ### Handle API Request for Product Info and	###
    ### Management								###
    ### A. Caravello 5/7/2009               	###
    ###############################################
	$_package = array(
		"name"		=> "session",
		"version"	=> "0.1.0",
		"release"	=> "2015-03-24"
	);

	app_log($_REQUEST['action']." request:".print_r($_REQUEST,true),'debug',__FILE__,__LINE__);

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
		$response->message = "PING RESPONSE";
		$response->success = 1;
		header('Content-Type: application/xml');
		print XMLout($response);
	}

	###################################################
	### Get session info by session code			###
	###################################################
	function getSession() {
		$session = new Session();
		$session->get($_REQUEST['code']);
		$response->success = 1;
		$response->session = $session;
        api_log($response);
        header('Content-Type: application/xml');
        print XMLout($response);
	}

	###################################################
	### Get session info by session code			###
	###################################################
	function getSessionHits() {
		$session = new Session();
		$session->get($_REQUEST['code']);
		$hits = $session->hits();
		$response->success = 1;
		$response->hit = $hits;
        api_log($response);
        header('Content-Type: application/xml');
        print XMLout($response);
	}

	###################################################
	### Return Properly Formatted Error Message		###
	###################################################
	function error($message)
	{
		$_REQUEST["stylesheet"] = '';
		app_log($message,'error',__FILE__,__LINE__);
		$response->message = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print XMLout($response,array("stylesheet" => $_REQUEST["stylesheet"]));
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
