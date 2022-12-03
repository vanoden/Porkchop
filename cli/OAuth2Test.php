<?php
	###################################################
	### manage_roles.php							###
	### Add/Remove Specified privileges for a 		###
	### specified role.								###
	### A. Caravello 6/1/2022						###
	###################################################
	### This file and its contents belong to		###
	### Root Seven Technologies.					###
	###################################################
	### Modifications								###
	###################################################

	###################################################
	### Load Dependencies							###
	###################################################
	$_SERVER['SERVER_NAME'] = "localhost";

	# Load Config
	require '../config/config.php';

	# General Utilities
	require INCLUDES.'/functions.php';
	spl_autoload_register('load_class');

	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb-php/adodb.inc.php';

	###################################################
	### Connect to Logger							###
	###################################################
	$logger = \Site\Logger::get_instance(array('type' => APPLICATION_LOG_TYPE,'path' => APPLICATION_LOG));
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
	### Initialize Common Objects					###
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
		$logger->write("Error connecting to database: ".$_database->ErrorMsg(),'error');
		exit;
	}
	$logger->write("Database Initiated",'trace');

    $_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
    if ($_CACHE_->error()) $logger->write('Unable to initiate Cache client: '.$_CACHE_->error(),'error');
    $logger->write("Cache Initiated",'trace',__FILE__,__LINE__);

	###################################################
	### Main Procedure								###
	###################################################
	print('PHP Graph Tutorial'.PHP_EOL.PHP_EOL);

	initializeGraph();

	greetUser();

	$choice = -1;

	while ($choice != 0) {
	    echo('Please choose one of the following options:'.PHP_EOL);
	    echo('0. Exit'.PHP_EOL);
	    echo('1. Display access token'.PHP_EOL);
	    echo('2. List my inbox'.PHP_EOL);
	    echo('3. Send mail'.PHP_EOL);
	    echo('4. List users (requires app-only)'.PHP_EOL);
	    echo('5. Make a Graph call'.PHP_EOL);
		echo('6. List Groups I belong to'.PHP_EOL);

	    $choice = (int)readline('');

		switch ($choice) {
			case 1:
				displayAccessToken();
				break;
			case 2:
				listInbox();
				break;
			case 3:
				sendMail();
				break;
			case 4:
				listUsers();
				break;
			case 5:
				makeGraphCall();
				break;
			case 6:
				getMemberships();
				break;
			case 0:
			default:
				print('Goodbye...'.PHP_EOL);
		}
	}

	function initializeGraph(): void {
		\Microsoft\GraphHelper::initializeGraphForUserAuth();
	}

	function greetUser(): void {
		try {
			$user = \Microsoft\GraphHelper::getUser();
			print('Hello, '.$user->getDisplayName().'!'.PHP_EOL);

			// For Work/school accounts, email is in Mail property
			// Personal accounts, email is in UserPrincipalName
			$email = $user->getMail();
			if (empty($email)) {
				$email = $user->getUserPrincipalName();
			}
			print('Email: '.$email.PHP_EOL.PHP_EOL);
		} catch (Exception $e) {
			print('Error getting user: '.$e->getMessage().PHP_EOL.PHP_EOL);
		}
	}

	function displayAccessToken(): void {
		try {
			$token = \Microsoft\GraphHelper::getUserToken();
			print('User token: '.$token.PHP_EOL.PHP_EOL);
		} catch (Exception $e) {
			print('Error getting access token: '.$e->getMessage().PHP_EOL.PHP_EOL);
		}
	}

	function listInbox(): void {
		// TODO
	}

	function sendMail(): void {
		// TODO
	}

	function listUsers(): void {
		// TODO
	}

	function makeGraphCall(): void {
		// TODO
	}

	function getMemberships(): void {
		try {
			$token = \Microsoft\GraphHelper::getUserGroups();
			print('User token: '.$token.PHP_EOL.PHP_EOL);
		} catch (Exception $e) {
			print('Error getting access token: '.$e->getMessage().PHP_EOL.PHP_EOL);
		}
	}