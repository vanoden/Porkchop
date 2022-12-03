<?php
	###################################################
	### de_version.php								###
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

	# Debug Variables
	$_debug_queries = array();

	###################################################
	### User Input									###
	###################################################

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
	$GLOBALS['_SESSION_'] = new \Site\Session();
	$GLOBALS['_SESSION_']->elevate();

	$db_service = new \Database\Service();
	$db_version = $db_service->version();
	print "Version: ".$db_version."\n";
	print "Supports password(): ";
	if ($db_service->supports_password()) print "Yes\n";
	else print "No\n";
