<?php
	###################################################
	### environment_test.php						###
	### Test access to resources required by site.	###
	### A. Caravello 1/23/2024						###
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

	# Set Server Environment
	$_SERVER['HTTP_HOST'] = "localhost";
	$_SERVER['SERVER_NAME'] = $GLOBALS['_config']->site->hostname;
	$_SERVER['HTTP_USER_AGENT'] = "cron";

	# General Utilities
	require INCLUDES.'/functions.php';
	spl_autoload_register('load_class');

	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb-php/adodb.inc.php';

	# Debug Variables
	$_debug_queries = array();

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
	### Main Procedure								###
	###################################################
	$_SESSION_ = new \Site\Session();
	$_SESSION_->elevate();
	$logger->writeln("Session initiated",'trace',__FILE__,__LINE__);

	$database = new \Database\Service();

	$connection_counter = new \Site\Counter("site.connections");
	$sql_error_counter = new \Site\Counter("sql.errors");
	$denied_counter = new \Site\Counter("permission_denied");
	$counter_404 = new \Site\Counter("return404");
	$counter_403 = new \Site\Counter("return403");
	$counter_500 = new \Site\Counter("return500");
	$auth_failed_counter = new \Site\Counter("auth_failed");
	$auth_blocked_counter = new \Site\Counter("auth_blocked");

	$counter = new \stdClass();
	$counter->connections = $connection_counter->get();
	$counter->sql_errors = $sql_error_counter->get();
	$counter->permission_denied = $denied_counter->get();
	$counter->code_404 = $counter_404->get();
	$counter->code_403 = $counter_403->get();
	$counter->code_500 = $counter_500->get();
	$counter->auth_failed = $auth_failed_counter->get();
	$counter->auth_blocked = $auth_blocked_counter->get();

	$cache = $_CACHE_->stats();
	$db = new \stdClass();
	$db->version = $database->version();
	$db->uptime = $database->global('uptime');
	$db->queries = $database->global('queries');
	$db->slow_queries = $database->global('slow_queries');
	$db->connections = $database->global('connections');
	$db->com_select = $database->global('Com_select');
	$db->com_insert = $database->global('Com_insert');
	$db->com_update = $database->global('Com_update');
	$db->com_replace = $database->global('Com_replace');
	$db->aborted_connects = $database->global('Aborted_connects');
	$db->threads_connected = $database->global('Threads_connected');
	$db->threads_running = $database->global('Threads_running');
	$apache = new \stdClass();
	$apache->version = apache_get_version();

	$response = new \APIResponse();
	$response->addElement('counter',$counter);
	$response->addElement('cache',$cache);
	$response->addElement('database',$db);
	$response->addElement('apache',$apache);
	$response->print();