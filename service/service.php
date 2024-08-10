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
	$_SERVER['HTTP_USER_AGENT'] = "sensor_node/0.1";
	$_SERVER['SERVER_NAME'] = "localhost";

	require '../config/config.php';

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
		print "Error connecting to database:<br>\n";
		print $_database->ErrorMsg();
		$logger->writeln("Error connecting to database: ".$_database->ErrorMsg(),'error');
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

	echo "service.php v0.0.1\n";

	// Open For Business
	$available = true;
	

	/* Allow the script to hang around waiting for connections. */
	set_time_limit(0);

	/* Turn on implicit output flushing so we see what we're getting
	* as it comes in. */
	ob_implicit_flush();

	print "Listening at ".$GLOBALS['_config']->service->address.":".$GLOBALS['_config']->service->port."\n";
	if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) === false) {
		echo "socket_create() failed: reason: " . socket_strerror(socket_last_error()) . "\n";
	}

	if (socket_bind($sock, $GLOBALS['_config']->service->address, $GLOBALS['_config']->service->port) === false) {
		echo "socket_bind() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
	}

	if (socket_listen($sock, 5) === false) {
		echo "socket_listen() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
	}

	do {
		if (($msgsock = socket_accept($sock)) === false) {
			echo "socket_accept() failed: reason: " . socket_strerror(socket_last_error($sock)) . "\n";
			break;
		}
		/* Send instructions. */
		//$msg = "\nWelcome to the PHP Test Server. \n" .
		//	"To quit, type 'quit'. To shut down the server type 'shutdown'.\n";
		//socket_write($msgsock, $msg, strlen($msg));

		do {
			if (false === ($buf = socket_read($msgsock, 2048, PHP_BINARY_READ))) {
				echo "socket_read() failed: reason: " . socket_strerror(socket_last_error($msgsock)) . "\n";
				break 2;
			}
			if (!$buf = trim($buf)) {
				continue;
			}
			if ($buf == 'quit') {
				break;
			}
			if ($buf == 'shutdown') {
				socket_close($msgsock);
				break 2;
			}

			$factory = new \Document\S4Factory();
			if ($factory->parseEnvelope($buf)) {
				$request = $factory->getRequest();

				print "Got ".$request->typeName()."\n";
				print "Asset ID: ".$request->assetId()."\n";
				print "Sensor ID: ".$request->sensorId()."\n";
				print "Reading Value: ".$request->value()."\n";
				print "Timestamp: ".$request->timestamp()."\n";
				$talkback = sprintf("%b%02b%02b%02b%016b%b%02b%b",1,1,99,0,1,2,0,3,1,4);
			socket_write($msgsock, $talkback, strlen($talkback));
				break;
			}
			else {
				$logger->writeln("Error parsing request: ".$factory->error(),'error');
				print("Error parsing request: ".$factory->error()."\n");
				break;
			}
			echo "$buf\n";
		} while (true);
		socket_close($msgsock);
	} while (true);

	socket_close($sock);
?>
