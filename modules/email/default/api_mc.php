<?php
    ###############################################
    ### Handle API Request for Email			###
    ### communications							###
    ### A. Caravello 1/21/2015               	###
    ###############################################
	$_package = array(
		"name"		=> "email",
		"version"	=> "0.2.1",
		"release"	=> "2019-12-17"
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
		error_log("Method ".$_REQUEST['method']." called by user ".$GLOBALS['_SESSION_']->customer->code);
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
		$response = new \HTTP\Response();

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
		if (empty($transport)) app_error("Invalid transport");
		if (isset($GLOBALS['_config']->email->hostname)) $transport->hostname($GLOBALS['_config']->email->hostname);
		if (isset($GLOBALS['_config']->email->username)) $transport->username($GLOBALS['_config']->email->username);
		if (isset($GLOBALS['_config']->email->password)) $transport->password($GLOBALS['_config']->email->password);
		if (isset($GLOBALS['_config']->email->token)) $transport->token($GLOBALS['_config']->email->token);
		if (! $transport->deliver($email)) {
			app_error($transport->error(),__FILE__,__LINE__);
			$response->success = 0;
			$response->error = $transport->error();
		}
		else {
			$response->success = 1;
			$response->result = $transport->result;
		}

		header('Content-Type: application/xml');
		print formatOutput($response);
	}

	###################################################
	### Get Emails									###
	###################################################
	function findQueueMessages() {
		$response = new \HTTP\Response();

		$queue = new \Email\Queue();
		$messages = $queue->messages();
		$response->success = 1;
		$response->message = $messages;
		
		print formatOutput($response);
	}

	###################################################
	### Get Next Queued Email						###
	###################################################
	function nextUnsent() {
		$response = new \HTTP\Response();

		$queue = new \Email\Queue();
		$message = $queue->takeNextUnsent();
		$response->success = 1;
		if ($message->id) $response->message = $message;
		
		print formatOutput($response);
	}

	###################################################
	### Record Delivery Outcome						###
	###################################################
	function deliveryEvent() {
		$response = new \HTTP\Response();

		$message = new \Email\Queue\Message($_REQUEST['id']);
		if ($message->recordEvent(
			$_REQUEST['status'],
			$_REQUEST['code'],
			$_REQUEST['host'],
			$_REQUEST['response']
		)) {
			$response->success = 1;
		}
		else {
			$response->success = 0;
			$response->error = $message->error();
		}
		print formatOutput($response);
	}

	###################################################
	### Manage Email Schema						###
	###################################################
	function schemaVersion() {
		$schema = new \Email\Schema();
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
		$schema = new \Email\Schema();
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
    	$_xml = new XML_Unserializer($options);
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
?>
