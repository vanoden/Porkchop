<?php
    ###############################################
    ### Handle API Request for Email			###
    ### communications							###
    ### A. Caravello 1/21/2015               	###
    ###############################################
	$_package = array(
		"name"		=> "email",
		"version"	=> "0.1.2",
		"release"	=> "2015-01-21"
	);

	app_log("Request: ".print_r($_REQUEST,true),'debug');

	###############################################
	### Load API Objects						###
    ###############################################
	# Default Response Values
	$response->success = 0;
	$response->method = $_REQUEST["method"];

	# Call Requested Event
	if ($_REQUEST["method"])
	{
		error_log("Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code." for asset ".$_REQUEST['asset_code']);
		# Call the Specified Method
		$function_name = $_REQUEST["method"];
		$function_name();
		exit;
	}
	# Only Developers Can See The API
	elseif (! $GLOBALS['_SESSION_']->customer->has_role('email manager')) {
		header("location: /_email/home");
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
		print formatOutput($response);
	}

	###################################################
	### Send Email									###
	###################################################
	function sendEmail() {
		$parameters = array();
		if ($_REQUEST['to']) $parameters['to'] = $_REQUEST['to'];
		if ($_REQUEST['from']) $parameters['from'] = $_REQUEST['from'];
		if ($_REQUEST['body']) $parameters['body'] = $_REQUEST['body'];
		if ($_REQUEST['subject']) $parameters['subject'] = $_REQUEST['subject'];

		$email = new \Email\Message();
		$email->to($_REQUEST['to']);
		$email->from($_REQUEST['from']);
		$email->subject($_REQUEST['subject']);
		$email->body($_REQUEST['body']);
		
		$transport = \Email\Transport::Create(array("provider" => $GLOBALS['_config']->email->provider));
		if (isset($GLOBALS['_config']->email->hostname)) $transport->hostname($GLOBALS['_config']->email->hostname);
		if (isset($GLOBALS['_config']->email->username)) $transport->username($GLOBALS['_config']->email->username);
		if (isset($GLOBALS['_config']->email->password)) $transport->password($GLOBALS['_config']->email->password);
		if (isset($GLOBALS['_config']->email->token)) $transport->token($GLOBALS['_config']->email->token);
		$transport->deliver($email);
		if ($transport->error()) app_error($transport->error(),__FILE__,__LINE__);

		$response = new \HTTP\Response();
		$response->success = 1;
		$response->result = $transport->result;

		header('Content-Type: application/xml');
		print formatOutput($response);
	}

	###################################################
	### Schema Info					###
	###################################################
	function schemaVersion() {
		$response = new \HTTP\Response();
		$response->success = 1;
		$response->version = 0;
		header('Content-Type: application/xml');
		print formatOutput($response);
	}

	###################################################
	### System Time									###
	###################################################
	function system_time() {
		return date("Y-m-d H:i:s");
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
		$response = new \HTTP\Response();
		$response->message = $message;
		$response->success = 0;
		header('Content-Type: application/xml');
		print formatOutput($response,array("stylesheet" => $_REQUEST["stylesheet"]));
		exit;
	}
	###################################################
	### Convert Object to XML						###
	###################################################
	function XMLout($object,$user_options='') {
		if (0) {
			$fp = fopen('/var/log/api/monitor.log', 'a');
			fwrite($fp,"#### RESPONSE ####\n");
			fwrite($fp, print_r($object,true));
			fclose($fp);
		}

		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_SERIALIZER_OPTION_INDENT        => '    ',
    	    XML_SERIALIZER_OPTION_RETURN_RESULT => true,
			XML_SERIALIZER_OPTION_MODE			=> 'simplexml',
			'rootName'							=> 'opt'
    	);
    	$xml = new XML_Serializer($options);
	   	if ($xml->serialize($object)) {
			$output = $xml->getSerializedData();
			return $output;
		}
	}
	###################################################
	### Convert XML to Object						###
	###################################################
	function XMLin($string,$user_options = array()) {
		require 'XML/Unserializer.php';
    	require 'XML/Serializer.php';
    	$options = array(
    	    XML_UNSERIALIZER_OPTION_RETURN_RESULT => false,
			XML_UNSERIALIZER_OPTION_COMPLEXTYPE => 'object'
    	);
    	$_xml = &new XML_Unserializer($options);
	   	if ($_xml->unserialize($string)) {
			//error_log("Returning ".$xml->getSerializedData());
			$object = $xml->getUnserializedData();
			return $object;
		}
		else {
			error("Invalid xml in request");
		}
	}

	function formatOutput($object,$options = '') {
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
