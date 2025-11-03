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

	declare(ticks = 1);
    //pcntl_signal(SIGTERM, "signal_handler");
    //pcntl_signal(SIGINT, "signal_handler");
	////pcntl_signal(SIGKILL, "signal_handler");
	//pcntl_signal(SIGHUP, "signal_handler");

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

	# Fetch IP Address of Server
	exec('/usr/sbin/ip addr show|/usr/bin/grep "inet "|/usr/bin/grep -v 127|/usr/bin/awk \'{print $2}\'|/usr/bin/cut -d\'/\' -f1',$ips);

	# Listener Config
	if (! isset($GLOBALS['_config'])) {
		$GLOBALS['_config'] = new \stdClass();
	}
	if (! isset($GLOBALS['_config']->service)) {
		$GLOBALS['_config']->service = new \stdClass();
		$GLOBALS['_config']->service->address = $ips[0];
		$GLOBALS['_config']->service->port = 12345;
		$GLOBALS['_config']->log_level = APPLICATION_LOG_LEVEL;
		$GLOBALS['_config']->log_type = APPLICATION_LOG_TYPE;
		$GLOBALS['_config']->console = false;
	}

	foreach ($argv as $arg) {
		if (preg_match('/^\-\-([\w\-\.]+)\=(.+)/',$arg,$matches)) {
			if ($matches[1] == 'address') $GLOBALS['_config']->service->address = $matches[2];
			elseif ($matches[1] == 'port') $GLOBALS['_config']->service->port = $matches[2];
			elseif ($matches[1] == 'log-level') {
				if (preg_match('/^(\d+)$/',$matches[2])) {
					switch($matches[2]) {
						case 9:
							$GLOBALS['_config']->log_level = 'trace2';
							break;
						case 8:
							$GLOBALS['_config']->log_level = 'trace';
							break;
						case 7:
							$GLOBALS['_config']->log_level = 'debug';
							break;
						case 6:
							$GLOBALS['_config']->log_level = 'info';
							break;
						case 5:
							$GLOBALS['_config']->log_level = 'notice';
							break;
						case 4:
							$GLOBALS['_config']->log_level = 'warning';
							break;
						case 3:
							$GLOBALS['_config']->log_level = 'error';
							break;
						case 2:
							$GLOBALS['_config']->log_level = 'critical';
							break;
						case 1:
							$GLOBALS['_config']->log_level = 'alert';
							break;
						case 0:
							$GLOBALS['_config']->log_level = 'emergency';
							break;
						default:
							$GLOBALS['_config']->log_level = APPLICATION_LOG_LEVEL;
							break;
					}
				}
				elseif (preg_match('/^(trace2|trace|debug|info|notice|warning|error|critical|alert|emergency)$/i',$matches[2])) {
					$GLOBALS['_config']->log_level = $matches[2];
				}
				else {
					print "Log level ".$matches[2]." not recognized\n";
					exit;
				}
			}
			else {
				print "Option '".$matches[1]."' not recognized\n";
				exit;
			}
		}
		elseif (preg_match('/^\-\-([\w\-\.]+)/',$arg,$matches)) {
			if ($matches[1] == 'console') $GLOBALS['_config']->log_type = 'screen';
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
	if (! defined('APPLICATION_LOG_TYPE')) define('APPLICATION_LOG_TYPE','syslog');
	if (! defined('APPLICATION_LOG')) define('APPLICATION_LOG','');
	$logger = \Site\Logger::get_instance(array('type' => $GLOBALS['_config']->log_type,'path' => APPLICATION_LOG,'host' => APPLICATION_LOG_HOST, 'port' => APPLICATION_LOG_PORT, 'level' => $GLOBALS['_config']->log_level));
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

	app_log("service.php v0.0.5");

	// Open For Business
	$available = true;

	// Configuration From Database
	$company = new \Company\Company(1);
	list($location) = $company->locations();
	$location = new \Company\Location();
	if (! $location->get('binary')) {
		if (! $location->add(array(
			'company_id' => $company->id(),
			'code' => 'binary',
			'name' => 'Binary Service'
		))) {
			app_log("Failed to add location: ".$location->error(),'error');
			$available = false;
		}
	}

	list($domain) = $company->domains();
	$_SERVER['HTTP_HOST'] = $domain->name();
	$_SERVER['SERVER_NAME'] = $domain->name();

	// Temporarily Local Session Table
	$sessionList = new \S4Engine\SessionList();
	$clientList = new \S4Engine\ClientList();

	/* Allow the script to hang around waiting for connections. */
	set_time_limit(0);

	/* Turn on implicit output flushing so we see what we're getting
	* as it comes in. */
	ob_implicit_flush();

	app_log("Listening at ".$GLOBALS['_config']->service->address.":".$GLOBALS['_config']->service->port);
	if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
		echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
	}

	// Trying to reuse the address to avoid "Address already in use" error
	$sockopt = socket_get_option($sock, SOL_SOCKET, SO_REUSEADDR);
	if ($sockopt === false) {
		echo 'Unable to get socket option: '. socket_strerror(socket_last_error()) . PHP_EOL;
	}
	elseif ($sockopt !== 0) {
		echo 'SO_REUSEADDR is set on socket !' . PHP_EOL;
	}

	// Bind service to the local address and specified port
	if (socket_bind($sock, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port) === false) {
		echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
		socket_close($sock);
		exit;
	}

	// Listen for incoming connections
	if (socket_listen($sock, 5) === false) {
		echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
	}

	// Tracking variable initialization
	$prevBuf = "";		// Previous buffer contents for comparison
	$buffer = "";		// Incoming data buffer
	$lastByteTime = 0;	// Time of last byte received
	$dbWatchdogInterval = 300; // Seconds between reconnecting to database
	$dbWatchdogLast = time();

	app_log("Socket Server listening on ".$GLOBALS['_config']->service->address.":".$GLOBALS['_config']->service->port);
	// Main Loop
	do {
		if (time() - $dbWatchdogLast > $dbWatchdogInterval) {
			// Simple Status Check to make sure connection to database is still alive
			$db = new \Database\Service();
			$db_uptime = $db->global("uptime");
			if ($db->error()) {
				app_log("Lost connection to database, attempting to reconnect: ".$db->error(),'error');
				$_database->Close();
				$_database->Connect(
					$GLOBALS['_config']->database->master->hostname,
					$GLOBALS['_config']->database->master->username,
					$GLOBALS['_config']->database->master->password,
					$GLOBALS['_config']->database->schema
				);
				if ($_database->ErrorMsg()) {
					app_log("Error reconnecting to database: \n".$_database->ErrorMsg(),'error');
					$available = false;
				}
				else {
					app_log("Reconnected to database",'info');
					$available = true;
				}
			}
			else {
				app_log("Database connection is healthy, uptime ".$db_uptime,'debug');
			}
			$dbWatchdogLast = time();
		}

		// New Connection
		if (($msgsock = socket_accept($sock)) === false) {
			echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
			break;
		}
		socket_getpeername($msgsock, $addr, $port);
		$_SERVER['REMOTE_ADDR']	= $addr;
		app_log("-----------------Connection from $addr:$port--------------------------",'info');

		// Process incoming data
		do {
			// Make sure _SESSION_ not carried over from previous request
			$_SESSION_ = null;

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
					print "Buffer: ";
					for ($i = 0; $i < strlen($incoming); $i ++) {
						$msg .= "[".ord(substr($incoming,$i,1))."]";
					}
					print "\n";
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
			$s4Engine = new \S4Engine\Engine();
			$s4Engine->serverId(99);

			// Debugging
			//app_log($s4Engine->printChars($buffer),'info');

			//app_log("ENG IN :  TYPE: Unknown SERVER: ".$s4Engine->serverId()." CLIENTID: ".$s4Engine->clientId()." SESSIONCODE: ".$s4Engine->sessionCodeDebug(),'info');

			// See if we have a full message and parse it if so
			if ($s4Engine->parse($buffer)) {
				//app_log("Getting message from engine");
				$request = $s4Engine->getMessage();

				if (empty($request)) {
					app_log("Got no message back!",'error');
					$response = new \Document\S4\BadRequestResponse();

					$s4Engine->setMessage($response);
					$envSize = $s4Engine->serialize($envelope);
					socket_write($msgsock, $envelope, $envSize);
					break;
				}
				// See if we have a session for this client
				elseif ($s4Engine->clientId() > 0) {
					// Get the client
					app_log("Looking for client ".$s4Engine->clientId(),'info');
					$client = new \S4Engine\Client();
					if (!$client->get($s4Engine->clientId())) {
						app_log("Failed to load client instance: ".$client->error(),'error');
						$response = new \Document\S4\BadRequestResponse();
						$s4Engine->setMessage($response);
						app_log("ERR OUT:  TYPE: ".$request->typeName()." SERVER: ".$s4Engine->serverId()." CLIENTID: ".$client->id()." CLIENTNUM: ".$client->number(),'info');
	
						$envSize = $s4Engine->serialize($envelope);
						socket_write($msgsock, $envelope, $envSize);
						break;
					}
					app_log("FOUND CLIENT:  TYPE: ".$request->typeName()." SERVER: ".$s4Engine->serverId()." CLIENTID: ".$client->id()." CLIENTNUM: ".$client->number(),'info');

					// Get the session
					app_log("Looking for session for client ".$client->id().", session code ".$s4Engine->sessionCodeDebug(),'info');
					$session = new \S4Engine\Session();
					if ($session->getSession($client->id(),$s4Engine->sessionCodeArray())) {
						// Populate Global Variable for Application
						$_SESSION_ = $session->portalSession();

						app_log("Loading existing session",'info');
						// Session code, Client Id contained in session
						$client = $session->client();

						app_log("FOUND SESSION:  TYPE: ".$request->typeName()."SERVER: ".$s4Engine->serverId()." CLIENTID: ".$client->id()." CLIENTNUM: ".$client->number()." SESSIONCODE: ".$session->codeDebug()." SESSIONNUM: ".$session->id(),'info');

						if ($client->id() < 1) {
							app_log("Failed to load client instance: ".$client->error(),'error');
							$response = new \Document\S4\BadRequestResponse();
							$s4Engine->setMessage($response);
							$envSize = $s4Engine->serialize($envelope);
							socket_write($msgsock, $envelope, $envSize);
							break;
						}

						//print_r($session->portalSession());
					}
					else {
						// Create a new session
						app_log("Creating a new session",'info');
						$client = new \S4Engine\Client();
						if (is_null($client)) {
							app_log("Failed to create client instance: ".$client->error(),'error');
							$response = new \Document\S4\BadRequestResponse();
							$s4Engine->setMessage($response);
							$envSize = $s4Engine->serialize($envelope);
							socket_write($msgsock, $envelope, $envSize);
							break;
						}
						elseif ($client->id() < 1) {
							app_log("Client was created without an id",'error');
							$response = new \Document\S4\BadRequestResponse();
							$s4Engine->setMessage($response);
							$envSize = $s4Engine->serialize($envelope);
							socket_write($msgsock, $envelope, $envSize);
							break;
						}
						$session = $sessionList->addInstance(array('client_id' => $client->id()));
					}
					//app_log("DUN:  TYPE: ".$request->typeName()." SERVER: ".$s4Engine->serverId()." CLIENTID: ".$s4Engine->session()->client()->id()." CLIENTNUM: ".$client->number()." SESSIONCODE: ".$session->codeDebug()." SESSIONNUM: ".$session->id(),'info');
				}

				app_log("Received ".$request->typeName()."!",'info');
				$envelope = "";		// Outgoing message buffer

				/****************************************/
				/* Process the specific request			*/
				/****************************************/
				switch($request->typeId()) {
					case 1:			// Register Request
						app_log("Register Request: Serial Number: ".$request->serialNumber()." Model Number: ".$request->modelNumber(),'info');
						$response = new \Document\S4\RegisterResponse();
						$response->serialNumber($request->serialNumber());
						$response->modelNumber($request->modelNumber());

						// Create a new client
						app_log("Creating a new client",'info');
						$client = new \S4Engine\Client();
						if (is_null($client)) {
							app_log("Failed to create client instance",'error');
							$response = new \Document\S4\BadRequestResponse();
							$s4Engine->setMessage($response);
							$envSize = $s4Engine->serialize($envelope);
							socket_write($msgsock, $envelope, $envSize);
							break;
						}
						$client->add(array(
							'serial_number' => $request->serialNumber(),
							'model_number' => $request->modelNumber(),
						));
						if ($client->error()) {
							if ($client->errorType() == 'MySQL Unavailable') {
								$response = new \Document\S4\SystemErrorResponse();
								$response->success(false);
								$envSize = $s4Engine->serialize($envelope);
								socket_write($msgsock, $envelope, $envSize);
								exit;
							}
							else {
								$response = new \Document\S4\BadRequestResponse();
								app_log("Failed to create client instance: ".$client->error(),'error');
							}
							$s4Engine->setMessage($response);
							$envSize = $s4Engine->serialize($envelope);
							socket_write($msgsock, $envelope, $envSize);
							break;
						}
						// Create a new session
						app_log("Creating a session",'debug');
						$session = $sessionList->addInstance(array('client_id' => $client->id()));
						if ($session->error()) {
							app_log("Failed to create session instance: ".$session->error(),'error');
							$response = new \Document\S4\BadRequestResponse();
							$s4Engine->setMessage($response);
							$envSize = $s4Engine->serialize($envelope);
							socket_write($msgsock, $envelope, $envSize);
							break;
						}
						else {
							//app_log(print_r($session,true),'info');
						}

						// Apply Session To Engine
						$s4Engine->session($session);

						// Set Message
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						//app_log("OUT:  TYPE: ".$response->typeName()."SERVER: ".$s4Engine->serverId()." CLIENTID: ".$s4Engine->session()->client()->id()." CLIENTNUM: ".$s4Engine->session()->client()->number()." SESSIONCODE: ".$session->codeDebug()." SESSIONNUM: ".$session->id(),'info');

						app_log("Returning $envSize byte ".$response->typeName()." to client");
						$s4Engine->printChars($envelope);
						$written = socket_write($msgsock, $envelope, $envSize);
						app_log("Wrote $written bytes",'info');
						break;

					case 3:			// Ping Request
						$response = new \Document\S4\PingResponse();

						// Apply Session Instance
						$s4Engine->session($session);

						// Set Message
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						app_log("Returning $envSize byte ".$response->typeName()." to client");
						$s4Engine->printChars($envelope);
						socket_write($msgsock, $envelope, $envSize);
						break;

					case 5:			// Reading Post
						app_log("Reading Post: Asset ID: ".$request->assetId()." Sensor ID: ".$request->sensorId()." Value: ".$request->value()." Timestamp:" .$request->timestamp(),'info');

						// Is Session Authenticated?
						if ($session->userId() < 1) {
							app_log("Unauthorized Reading Post",'notice');
							$response = new \Document\S4\Unauthorized();
						}
						else {
							app_log("Finding asset ".$request->assetId(),'info');
							$asset = new \Monitor\Asset((int)$request->assetId());
							if (!$asset->exists()) {
								app_log("Asset not found",'notice');
								$response = new \Document\S4\BadRequestResponse();
							}
							else {
								app_log("Finding sensor ".$request->sensorId(),'info');
								$sensor = new \Monitor\Sensor((int)$request->sensorId());
								if (!$sensor->exists()) {
									app_log("Sensor not found",'notice');
									$response = new \Document\S4\BadRequestResponse();
								}
								elseif ($sensor->asset()->id() != $asset->id()) {
									app_log("Sensor does not belong to asset",'notice');
									$response = new \Document\S4\BadRequestResponse();
								}
								else {
									app_log("Adding reading to asset ".$asset->id()." sensor ".$sensor->id(),'info');
									$reading = new \Monitor\Reading();
									$reading->value($request->value());
									$reading->timestamp($request->timestamp());
									$added = $sensor->addReading($reading);
									if (! is_null($added)) {
										app_log("Added reading successfully!",'info');
										$response = new \Document\S4\Acknowledgement();
										$response->success(true);
									}
									else {
										app_log("Failed to add reading: ".$reading->error(),'error');
										$response = new \Document\S4\SystemErrorResponse();
										$response->success(false);
									}
								}
							}
						}
						$s4Engine->session($session);
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						app_log("Returning $envSize byte ".$response->typeName()." to client");
						$s4Engine->printChars($envelope);
						$written = socket_write($msgsock, $envelope, $envSize);
						//print "Wrote $written bytes\n";
						break;
					case 11:		// Time Request
						app_log("Request for current time","info");
						$response = new \Document\S4\TimeResponse();
						$response->success(true);
						$s4Engine->session($session);
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						app_log("Returning $envSize byte ".$response->typeName()." to client","info");
						$written = socket_write($msgsock, $envelope, $envSize);
						break;
					case 13:		// Auth Request
						$customer = new \Register\Customer();
						app_log("Authenticating Customer '".$request->login()."' with 'TestPH3Node'");
						if ($customer->authenticate($request->login(),'TestPH3Node')) {
							app_log("Customer '".$customer->login()."' authenticated",'info');
							$session->portalSession()->assign($customer->id());
							$response = new \Document\S4\AuthResponse();
							$response->success(true);

							// Apply Session Instance
							//$session->add(array('client_id' => $client->id()));
							$s4Engine->session($session);
						}
						else {
							app_log("Invalid Login Attempt",'info');
							//$session->userId(0);
							$response = new \Document\S4\AuthResponse();
							$response->success(false);

							// New Session Instance
							$s4Engine->session($session);
						}

						// Set Message
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						app_log("Returning $envSize byte ".$response->typeName()." to client");
						$s4Engine->printChars($envelope);
						socket_write($msgsock, $envelope, $envSize);
						break;

					default:		// Unrecognized
						app_log("Unknown request type: ".$request->typeId(),'error');
						$response = new \Document\S4\BadRequestResponse();
						$s4Engine->setMessage($response);
						$envSize = $s4Engine->serialize($envelope);
						socket_write($msgsock, $envelope, $envSize);
						break;
				}
				break;
			}
			// Unparseable Request
			else {
				$logger->writeln("Error parsing request: ".$s4Engine->error(),'error');
				print("Error parsing request: ".$s4Engine->error()."\n");
				$response = new \Document\S4\BadRequestResponse();
				$s4Engine->setMessage($response);
				$envSize = $s4Engine->serialize($envelope);
				if ($envSize < 1) {
					$logger->writeln("Error serializing response: ".$s4Engine->error(),'error');
					print("Error serializing response: ".$s4Engine->error()."\n");
					break;
				}

				//app_log("OUT:  TYPE: ".$response->typeName()."SERVER: ".$s4Engine->serverId()." CLIENTID: ".$s4Engine->session()->client()->id()." CLIENTNUM: ".$client->number()." SESSIONCODE: ".$s4Engine->sessionCodeString()." SESSIONNUM: ".$session->number(),'info');

$s4Engine->printChars($envelope);
				socket_write($msgsock, $envelope, $envSize);
				break;
			}
			echo "$buffer\n";
		} while (true);
	
		app_log("--------------------------Request processed and response sent--------------------------",'info');
		socket_close($msgsock);
	} while (true);

	socket_close($sock);
	app_log("Application exited");

	/**
	 * Signal Handler - Close Socket on exit
	 * @param mixed $signal 
	 * @return void 
	 */
    function signal_handler($signal) {
		$sock = $GLOBALS['sock'];

        switch($signal) {
            case SIGTERM:
                print "Caught SIGTERM\n";
                exit;
            case SIGKILL:
                print "Caught SIGKILL\n";
                exit;
            case SIGINT:
                print "Caught SIGINT\n";
                exit;
			default:
				print "Caught signal $signal\n";
				exit;
        }
		print "Closing socket\n";
		socket_close($sock);
    }
