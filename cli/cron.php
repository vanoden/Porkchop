<?php
	###################################################
	### cron.php									###
	### This module is a content management and 	###
	### and display system.							###
	### A. Caravello 2/3/2012						###
	###################################################
	### This file and its contents belong to		###
	### Root Seven Technologies.					###
	###################################################
	### Modifications								###
	###################################################

	###################################################
	### Load Dependencies							###
	###################################################
	# Load Config
	require '../config/config.php';

	# Some HTTP Stuff
	$_SERVER['HTTP_HOST'] = "localhost";
	$_SERVER['REQUEST_URI'] = $argv[1];

	# General Utilities
	require INCLUDES.'/functions.php';
	spl_autoload_register('load_class');

	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb-php/adodb.inc.php';

	error_log("###### Page: ".$_SERVER["REQUEST_URI"]."######");
	error_log("\$_REQUEST: ".print_r($_REQUEST,true));

	# Debug Variables
	$_debug_queries = array();

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

	###################################################
	### Connect to Memcache if so configured		###
	###################################################
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error) test_fail('Unable to initiate Cache client: '.$_CACHE_->error);
	$logger->write("Cache Initiated",'trace',__FILE__,__LINE__);

	###################################################
	### Initialize Session							###
	###################################################
	$_SESSION_ = new \Site\Session();
	$_SESSION_->start();
	$logger->write("Session initiated",'trace',__FILE__,__LINE__);

	# Don't Cache this Page
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");

	# Create Session
	$_session = new Session($_COOKIE['session_code']);

	if ($_session->error)
	{
		error_log($_session->error);
		die("Session Error: ".$_session->error);
	}
	if ($_session->message)
	{
	    $page_message = $_session->message;
	}

	# Get Info about person
	$_customer = new Customer($_session->customer);

	# Load Page Information
	$page_parameters = array(
		    "auth_required" => 0,
		    "style"			=> ''
	    );
	$_page = new Page($page_parameters);
	if ($_page->error)
	{
		print "Error: ".$_page->error;
		error_log($_page->error);
		exit;   
	}

	print $_page->load_template();
?>
