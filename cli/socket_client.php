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
			else {
				print "Option '".$matches[1]."' not recognized\n";
				exit;
			}
		}
	}

	###################################################
    ### Connect to Logger                           ###
    ###################################################
    $logger = \Site\Logger::get_instance(array('type' => 'screen'));
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

	app_log("Creating Request");
	$factory = new \Document\S4Factory();
	$message = $factory->create("PingRequest");
	$message->content('12345');

	app_log("Packaging in Envelope");
	$s4Engine->setMessage($message);
	$s4Engine->serverId(99);
	$s4Engine->clientId(1);
	$msgLength = $s4Engine->serialize($content);
$s4Engine->arrayPrint($content);
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
	while (time() - $start_time < $timeout) {
		$buffer = socket_read($socket,1);
		if (strlen($buffer > 10) && $s4Engine->parse($buffer)) {
			$response = $s4Engine->getMessage();
			print "Got ".$response->typeName()." response\n";
			break;
		}
	}
	if (time() - $start_time >= $timeout) {
		app_log("Timeout waiting for response");
	}

	socket_close($socket);
?>