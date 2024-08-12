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

	$factory = new \Document\S4Factory();
	$message = $factory->create("PingRequest");
	$message->serverId(99);
	$message->clientId(1);
	$message->content('12345');

	// Create a new socket
	$socket = socket_create(AF_INET, SOCK_STREAM, 0);

	// Connect to the server
	socket_connect($socket, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port);

	// Write data to the server
	socket_write($socket, $message->build());

	while (true) {
		$buffer = socket_read($socked,255);
		if ($response = $s4Engine->parse($buffer)) {
			print "Got ".$response->typeName()." response\n";
			break;
		}
	}

	socket_close($sock);
?>
