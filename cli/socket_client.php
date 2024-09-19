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

use Document\S4\Message;

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

// General Utilities
require '../includes/functions.php';
spl_autoload_register('load_class');

// Server Config
if (! isset($GLOBALS['_config']->service)) {
	$GLOBALS['_config']->service = new \stdClass();
	$GLOBALS['_config']->service->address = '192.168.10.111';
	$GLOBALS['_config']->service->port = 12346;
	$GLOBALS['_config']->log_level = 'info';
}

// Load command line arguments
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
$s4Engine = new \S4Engine\Engine();
$factory = new \Document\S4Factory();
$session = new \S4Engine\Session();

/**
 * Ping Server
 */
if (false) {
	app_log("Sending Ping Request",'info');
	app_log("Creating Request");
	$message = $factory->create("PingRequest");

	app_log("Packaging in Envelope");
	$s4Engine->setMessage($message);

	$response = sendRequest($session,$message);
	if (!empty($response)) {
		app_log("Got ".$response->typeName()." response",'info');
	}
	else {
		app_log("Failed to get response",'error');
	}
}

/**
 * Send Reading to Server
 */
if (false) {
	$reading_message = $factory->create("ReadingPost");
	$reading_message->assetId(1);
	$reading_message->sensorId(1);
	$reading_message->value(123.45);
	$reading_message->timestamp(time());

	app_log("Sending Reading");
	$response = sendRequest($session,$reading_message);
	if (!empty($response)) {
		app_log("Got ".$response->typeName()." response",'info');
	}
	else {
		app_log("Failed to get response",'error');
	}
}

/**
 * Register Session With Server
 * RegisterRequest requires serialNumber and modelNumber
 * Expects RegisterResponse with matching serialNumber and modelNumber
 * as well as client id, server id and session code
 */
if (true) {
	app_log("Sending Registration Request",'info');

	// Create Registration Request
	$reg_request = $factory->create("RegisterRequest");
	$reg_request->serialNumber("tony-asus");
	$reg_request->modelNumber("TEST001");

	// Send Request to Server
	$response = sendRequest($session,$reg_request);
	if (!empty($response)) {
		// Gives us details!
		app_log("Got ".$response->typeName()." response",'info');
		app_log("Serial Number: ".$response->serialNumber(),'info');
		app_log("Model Number: ".$response->modelNumber(),'info');
	}
	else {
		app_log("Failed to get response",'error');
	}
}

/**
 * Authenticate to Server
 * AuthRequest required login and password
 * Expects AuthResponse
 */
if (false) {
	app_log("Sending Authentication Request",'info');

	// Create Authentication Request
	$auth_message = $factory->create("AuthRequest");
	$auth_message->login("http-test");
	$auth_message->password("Testing4U!");

	// Send Request to Server
	$response = sendRequest($session,$auth_message);
	if (!empty($response)) {
		app_log("Got ".$response->typeName()." response",'info');
	}
	else {
		app_log("Failed to get response",'error');
	}
}

/**
 * Package and Send a Request.  Parse and Return the Response
 * @param mixed $session 
 * @param null|Message $request 
 * @return null|string|Message 
 */
function sendRequest(\S4Engine\Session &$session,?\Document\S4\Message $request) {
	static $session;
	$newSession = true;			// Is this a new session?

	if (is_null($session)) {
		app_log("New session",'info');
		$session = new \S4Engine\Session();
		$newSession = true;
	}
	else {
		app_log("Existing session:\n".$session->summary(),'info');
		$newSession = false;
	}
	// Initialize S4 Engine for handling envelopes
	static $s4Engine;
	if (is_null($s4Engine)) {
		$s4Engine = new \S4Engine\Engine();
		$s4Engine->session($session);
	}
	else {
		app_log("Existing S4 Engine",'info');
		app_log("Client ID: ".$s4Engine->session()->client()->id(),'info');
	}

	// How long to wait for a response
	$timeout = 4;

	// Connect to Server
	$socket = socket_create(AF_INET, SOCK_STREAM, 0);
	if (! socket_connect($socket, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port)) {
		$error = socket_last_error();
		app_log("Error connecting to server: $error");
		return null;
	}
	app_log("Connected to server",'info');

	// Set and Send Request
	$s4Engine->setMessage($request);
	$content = "";
	$msgLength = $s4Engine->serialize($content);

	app_log("sendMessage(): Sending Request, $msgLength bytes",'info');
	socket_write($socket, $content, $msgLength);
	app_log("Sent $msgLength bytes, waiting for response",'info');

	// Wait for and Parse Complete Response
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
				print "S4Engine Client: ".$s4Engine->clientId()."\n";
				$response = $s4Engine->getMessage();
				app_log("Got '".$response->typeName()."' response",'info');
				if ($newSession) {
					// New Session
					print_r($s4Engine);
					$session = new \S4Engine\Session();
					$session->codeArray($s4Engine->session()->codeArray());
					print_r("Set Session Client ID to ".$s4Engine->clientId()."\n");
					$client = new \S4Engine\Client();
					$client->number($s4Engine->clientId());
					$session->client($client);
					print_r($session);
	
					app_log("New Session:\n".$session->summary(),'info');
					//app_log("Client ID: ".$s4Engine->session()->client()->id(),'info');
					//app_log("Server ID: ".$s4Engine->serverId(),'info');
					//app_log("Session Code: ".$s4Engine->session()->codeDebug(),'info');
				}
				else {
					app_log("Maintain Session:\n".$session->summary(),'info');
					app_log("Client ID: ".$session->client()->id(),'info');
				}
				break;
			}
			elseif ($s4Engine->error()) {
				app_log("Failed to parse response: ".$s4Engine->error(),'error');
				$response = null;
			}
		}
	}
	if (time() - $start_time >= $timeout) {
		app_log("Timeout waiting for response",'error');
		$response = null;
	}
	
	socket_close($socket);

	return $response;
}