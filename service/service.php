#!/usr/bin/php -q
<?php	/**
	 * service.php
	 * PHP Socket Service for Sensor Node
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
	###################################################
	### Load Dependencies							###
	###################################################
	# Load Config
	$_SERVER['HTTP_HOST'] = "localhost";
	$_SERVER['REQUEST_URI'] ='/';
	$_SERVER['HTTP_USER_AGENT'] = "sensor_node/0.2";
	$_SERVER['SERVER_NAME'] = "localhost";

	require 'config/config.php';

	# Spoof Server Variables
	$_SERVER['SERVER_NAME'] = $_config->site->hostname;

	# General Utilities
	require INCLUDES.'/functions.php';
	spl_autoload_register('load_class');

	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb-php/adodb.inc.php';

	# Listener Config
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
	### Initialize Site Instance					###
	###################################################
	$site = new \Site();

	###################################################
	### Connect to Logger							###
	###################################################
	if (! defined('APPLICATION_LOG_HOST')) define('APPLICATION_LOG_HOST','127.0.0.1');
	if (! defined('APPLICATION_LOG_PORT')) define('APPLICATION_LOG_PORT','514');
	$logger = \Site\Logger::get_instance(array('type' => APPLICATION_LOG_TYPE,'path' => APPLICATION_LOG,'host' => APPLICATION_LOG_HOST,'port' => APPLICATION_LOG_PORT));
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
	### Connect to Database							###
	###################################################
	# Connect to Database
	$_database = NewADOConnection('mysqli');
	if ($GLOBALS['_config']->database->master->port) $_database->port = $GLOBALS['_config']->database->master->port;
	$_database->Connect(
		$GLOBALS['_config']->database->master->hostname,
		$GLOBALS['_config']->database->master->username,
		$GLOBALS['_config']->database->master->password,
		$GLOBALS['_config']->database->schema
	);
	
	if ($_database->ErrorMsg()) {
		app_log("Error connecting to database: \n".$_database->ErrorMsg());
		exit;
	}
	$logger->writeln("Database Initiated",'trace');

	###################################################
	### Connect to Memcache if so configured		###
	###################################################
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error()) $logger->writeln('Unable to initiate Cache client: '.$_CACHE_->error(),'error');
	$logger->writeln("Cache Initiated",'trace',__FILE__,__LINE__);

	###################################################
	### Initialize Session							###
	###################################################
	$_SESSION_ = new \Site\Session();
	$_SESSION_->start();
	$logger->writeln("Session initiated",'trace',__FILE__,__LINE__);

	app_log("service.php v0.0.1");

	// Open For Business
	$available = true;

	// Temporarily Local Session Table
	$sessionTable = [];
	
	/* Allow the script to hang around waiting for connections. */
	set_time_limit(0);

	/* Turn on implicit output flushing so we see what we're getting
	* as it comes in. */
	ob_implicit_flush();

	app_log("Listening at ".$GLOBALS['_config']->service->address.":".$GLOBALS['_config']->service->port);
	if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
		echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
	}

	if (socket_bind($sock, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port) === false) {
		echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
	}

	if (socket_listen($sock, 5) === false) {
		echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
	}

	$prevBuf = "";
	$buffer = "";
	$lastByteTime = 0;
	do {
		if (($msgsock = socket_accept($sock)) === false) {
			echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
			break;
		}

		// Process incoming data
		do {
			// Leave loop on read error
			if (false === ($incoming = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
				echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
				break 2;
			}

			// Leave loop if no data
			if (empty($incoming)) {
				if (time() - $lastByteTime > 2) {
					app_log("Connection Timeout, clearing buffer");
					$msg = "";
					for ($i = 0; $i < strlen($incoming); $i ++) {
						$msg .= "[".ord(substr($incoming,$i,1))."]";
					}
					app_log($msg,'trace');
					break;
				}
				continue;
			}
			app_log("incoming: '".$incoming,"'");

			// Append received chars to buffer
			$lastByteTime == time();
			$buffer .= $incoming;

			if (!$buffer == trim($buffer)) {
				// Cleared some whitespace
				continue;
			}

			// Initialize S4 Engine for parsing/building messages
			$s4Engine = new \Document\S4();

			// Debugging
			$s4Engine->printChars($buffer);

			// See if we have a full message and parse it if so
			if ($s4Engine->parse($buffer)) {
				app_log("Getting message");
				$request = $s4Engine->getMessage();
if (empty($request)) app_log("Got no message back!",'error');

				// See if we have a session for this client
				if (!empty($sessionTable[$s4Engine->sessionId()])) {
					// Is Session Expired?
					if ($sessionTable[$s4Engine->sessionId()]['expires'] < time()) {
						unset($sessionTable[$s4Engine->sessionId()]);
					}
				}
				if (empty($sessionTable[$s4Engine->sessionId()])) {
					$sessionTable[$s4Engine->sessionId()] = array(
						'client' => $s4Engine->clientId(),
						'server' => $s4Engine->serverId(),
						'customer_id' => 0,
						'sequence'	=> 0,
						'expires' => time() + 43000
					);
				}

				app_log("Received ".$request->typeName()."!",'info');
				$envelope = "";
				switch($request->typeId()) {
					case 1:
						// Register Request
						$response = new \Document\S4\RegisterResponse();
						$response->serialNumber($request->serialNumber());
						$response->modelNumber($request->modelNumber());
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						app_log("Returning $envSize byte ".$response->typeName()." to client");
						$s4Engine->printChars($envelope);
						$written = socket_write($msgsock, $envelope, $envSize);
						print "Wrote $written bytes\n";
						break;
					case 3:
						// Ping Request
						$response = new \Document\S4\PingResponse();
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						socket_write($msgsock, $envelope, $envSize);
						break;
					case 5:
						// Reading Post
						app_log("Asset ID: ".$request->assetId());
						app_log("Sensor ID: ".$request->sensorId());
						app_log("Reading Value: ".$request->value());

						// Is Session Authenticated?
						if ($sessionTable[$s4Engine->sessionId()]['customer_id'] == 0) {
							app_log("Unauthorized Reading Post");
							$response = new \Document\S4\Unauthorized();
						}
						else {
							$asset = new \Monitor\Asset($request->assetId());
							if (!$asset->exists()) {
								$response = new \Document\S4\BadRequestResponse();
							}
							else {
								$sensor = new \Monitor\Sensor($request->sensorId());
								if (!$sensor->exists()) {
									$response = new \Document\S4\BadRequestResponse();
								}
								else {
									$reading = new \Monitor\Reading();
									$reading->assetId($asset->id());
									$reading->sensorId($sensor->id());
									$reading->value($request->value());
									$reading->timestamp($request->timestamp());
									if ($sensor->addReading($reading)) {
										$response = new \Document\S4\Acknowledgement();
									}
									else {
										$response = new \Document\S4\SystemErrorResponse();
									}
								}
							}
						}
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						app_log("Returning $envSize byte ".$response->typeName()." to client");
						$s4Engine->printChars($envelope);
						$written = socket_write($msgsock, $envelope, $envSize);
						print "Wrote $written bytes\n";
						break;
					case 13:
						// Auth Request
						$customer = new \Register\Customer();
						app_log("Authenticating Customer '".$request->login()."' with '".$request->password()."'");
						if ($customer->authenticate($request->login(),$request->password())) {
							app_log("Customer '".$customer->login()."' authenticated");
							$sessionTable[$s4Engine->sessionId()]['customer_id'] = $customer->id();
							$response = new \Document\S4\AuthResponse();
						}
						else {
							app_log("Invalid Login Attempt");
							$response = new \Document\S4\Unauthorized();
						}
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						socket_write($msgsock, $envelope, $envSize);
						break;
					default:
						app_log("Unknown request type: ".$request->typeId(),'error');
						$response = new \Document\S4\BadRequestResponse();
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						socket_write($msgsock, $envelope, $envSize);
						break;
				}
				app_log("Request processed and response sent");
				break;
			}
			else {
				$logger->writeln("Error parsing request: ".$s4Engine->error(),'error');
				print("Error parsing request: ".$s4Engine->error()."\n");
				$response = new \Document\S4\BadRequestResponse();
				$s4Engine->setMessage($response);
				$envSize = $s4Engine->serialize($envelope);
$s4Engine->printChars($envelope);
				socket_write($msgsock, $envelope, $envSize);
				break;
			}
			echo "$buffer\n";
		} while (true);
		socket_close($msgsock);
	} while (true);

	socket_close($sock);
	app_log("Application exited");
?>
