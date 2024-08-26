#!/usr/bin/php -q
<?php
/**
	 * socket_client.php
	 * PHP Socket Client to test socket service
	 * 
	 * @author		Anthony Caravello
	 * @version		0.0.1
	 * 
*/

	$timeout = 3;		// Seconds to wait for a response

    // PHP_VERSION_ID is available as of PHP 5.2.7, if version is lower than that, then emulate it
    if (!defined('PHP_VERSION_ID')) {
        $version = explode('.', PHP_VERSION);
        define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
    }

    // ignore all the isset warnings for now
    if (PHP_VERSION_ID > 70000) error_reporting(~E_DEPRECATED & ~E_NOTICE);

	error_reporting(E_ALL);

	define("MODE","S4");

	// Fake SERVER SuperGlobal
	$_SERVER = array(
		"HTTP_HOST"	=> 'localhost',
		"SERVER_NAME" => 'localhost'
	);

	###################################################
	### Load Dependencies							###
	###################################################
	require '../config/config.php';

	# General Utilities
	require '../includes/functions.php';
	spl_autoload_register('load_class');

	# Server Config
	if (! isset($GLOBALS['_config']->service)) {
		$GLOBALS['_config']->service = new \stdClass();
		$GLOBALS['_config']->service->address = '192.168.10.111';
		$GLOBALS['_config']->service->port = 12346;
	}

	foreach ($argv as $arg) {
		if (preg_match('/^\-\-([\w\-\.]+)\=(.+)/',$arg,$matches)) {
			if ($matches[1] == 'address') $GLOBALS['_config']->service->address = $matches[2];
			elseif ($matches[1] == 'port') $GLOBALS['_config']->service->port = $matches[2];
			elseif ($matches[1] == 'log-level') $GLOBALS['_config']->log_level = $matches[2];
			else {
				print "Option '".$matches[1]."' not recognized\n";
				exit;
			}
		}
	}

	###################################################
    ### Connect to Logger                           ###
    ###################################################
    $logger = \Site\Logger::get_instance(array('type' => 'screen', 'level' => $GLOBALS['_config']->log_level));
    if ($logger->error()) {
        error_log("Error initializing logger: ".$logger->error());
        print "Logger error\n";
        exit;
    }
    $logger->connect();
    if ($logger->error()) {
        error_log("Error initializing logger: ".$logger->error());
        print "Logger error\n";
        exit;
    }

	###################################################
	### Main Procedure								###
	###################################################
	// Initialize S4 Engine for handling envelopes
	$s4Engine = new \Document\S4();
	$factory = new \Document\S4Factory();

	/**
	 * Ping Server
	 */
	if (false) {
		app_log("Sending Ping Request",'info');
		app_log("Creating Request");
		$message = $factory->create("PingRequest");

		app_log("Packaging in Envelope");
		$s4Engine->setMessage($message);
		$s4Engine->serverId(99);
		$s4Engine->clientId(1);
		$msgLength = $s4Engine->serialize($content);
		// Create a new socket
		app_log("Connecting to ".$GLOBALS['_config']->service->address." at port ".$GLOBALS['_config']->service->port);
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);

		// Connect to the server
		if (! socket_connect($socket, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port)) {
			$error = socket_last_error();
			app_log("Error connecting to server: $error");
			exit;
		}
		app_log("Connected to server");

		// Write data to the server
		app_log("Sending request to server");
		socket_write($socket, $content, $msgLength);
		app_log("Sent $msgLength bytes, waiting for response");

		$start_time = time();
		$response = "";
		$buffer = "";
		while (time() - $start_time < $timeout) {
			$incoming = socket_read($socket,1);
			if (strlen($incoming) < 1) continue;
			$buffer .= $incoming;
			if (strlen($buffer) >= 18) {
				if ($s4Engine->parse($buffer)) {
					$buffer = "";
					$response = $s4Engine->getMessage();
					app_log("Got ".$response->typeName()." response",'info');
					break;
				}
				else {
					app_log("Failed to parse response: ".$s4Engine->error(),'error');
				}
			}
		}
		if (time() - $start_time >= $timeout) {
			app_log("Timeout waiting for response.",'error');
			app_log("Received only ".strlen($buffer)." bytes",'info');
			exit;
		}
	}

	/**
	 * Send Reading to Server
	 */
	if (false) {
		app_log("Sending Reading",'info');
		if (true) {
			socket_close($socket);
			$socket = socket_create(AF_INET, SOCK_STREAM, 0);
			if (! socket_connect($socket, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port)) {
				$error = socket_last_error();
				app_log("Error connecting to server: $error");
				exit;
			}
		}

		app_log("Connected to server");
		$reading_message = $factory->create("ReadingPost");
		$reading_message->assetId(1);
		$reading_message->sensorId(1);
		$reading_message->value(123.45);
		$reading_message->timestamp(time());
		$s4Engine->setMessage($reading_message);
		$msgLength = $s4Engine->serialize($content);
		app_log("Sending Reading");
		socket_write($socket, $content, $msgLength);
		app_log("Sent $msgLength bytes, waiting for response");

		$start_time = time();
		$response = "";
		$buffer = "";
		while (time() - $start_time < $timeout) {
			$incoming = socket_read($socket,1);
			if (strlen($incoming) < 1) continue;
			else continue;
			$buffer .= $incoming;
			if (strlen($buffer) >= 11) {
				if ($s4Engine->parse($buffer)) {
					$buffer = "";
					$response = $s4Engine->getMessage();
					app_log("Got ".$response->typeName()." response",'info');
					break;
				}
				else {
					app_log("Failed to parse response: ".$s4Engine->error(),'error');
				}
			}
		}
		if (time() - $start_time >= $timeout) {
			app_log("Timeout waiting for response",'error');
			app_log("Received only ".strlen($buffer)." bytes",'info');
			exit;
		}
		socket_close($socket);
	}

	/**
	 * Register Session With Server
	 */
	app_log("Sending Registration Request",'info');
	if (true) {
		$socket = socket_create(AF_INET, SOCK_STREAM, 0);
		if (! socket_connect($socket, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port)) {
			$error = socket_last_error();
			app_log("Error connecting to server: $error");
			exit;
		}
	}

	app_log("Connected to server");
	$reg_request = $factory->create("RegisterRequest");
	$reg_request->serialNumber("tony-asus");
	$reg_request->modelNumber("TEST001");
	$s4Engine->setMessage($reg_request);
	$msgLength = $s4Engine->serialize($content);
	app_log("Sending Registration Request");
	socket_write($socket, $content, $msgLength);
	app_log("Sent $msgLength bytes, waiting for response");

	$start_time = time();
	$response = "";
	$buffer = "";
	while (time() - $start_time < $timeout) {
		$incoming = socket_read($socket,1);
		if (strlen($incoming)< 1) continue;
		$buffer .= $incoming;
		if (strlen($buffer) > 14) {
			if ($s4Engine->parse($buffer)) {
				$buffer = "";
				$response = $s4Engine->getMessage();
				app_log("Got ".$response->typeName()." response",'info');
				app_log(print_r($response,true));
				app_log("Serial Number: ".$response->serialNumber());
				app_log("Model Number: ".$response->modelNumber());
				break;
			}
			else {
				app_log("Failed to parse response: ".$s4Engine->error(),'error');
			}
		}
	}
	if (time() - $start_time >= $timeout) {
		app_log("Timeout waiting for response",'error');
		exit;
	}

	socket_close($socket);

	/**
	 * Authenticate to Server
	 */
	if (false) {
		app_log("Sending Authentication Request",'info');
		if (true) {
			$socket = socket_create(AF_INET, SOCK_STREAM, 0);
			if (! socket_connect($socket, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port)) {
				$error = socket_last_error();
				app_log("Error connecting to server: $error");
				exit;
			}
		}

		app_log("Connected to server");
		$auth_message = $factory->create("AuthRequest");
		$auth_message->login("http-test");
		$auth_message->password("Testing4U!");
		$s4Engine->setMessage($auth_message);
		$msgLength = $s4Engine->serialize($content);
		app_log("Sending Auth Request");
		socket_write($socket, $content, $msgLength);
		app_log("Sent $msgLength bytes, waiting for response");

		$start_time = time();
		$response = "";
		$buffer = "";
		while (time() - $start_time < $timeout) {
			$incoming = socket_read($socket,1);
			if (strlen($incoming)< 1) continue;
			$buffer .= $incoming;
			if (strlen($buffer) > 11) {
				if ($s4Engine->parse($buffer)) {
					$buffer = "";
					$response = $s4Engine->getMessage();
					app_log("Got ".$response->typeName()." response",'info');
					break;
				}
				else {
					app_log("Failed to parse response: ".$s4Engine->error(),'error');
				}
			}
		}
		if (time() - $start_time >= $timeout) {
			app_log("Timeout waiting for response",'error');
			exit;
		}

		socket_close($socket);
	}
?>
