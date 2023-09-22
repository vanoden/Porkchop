<?php
	###################################################################
	### html/index.php												###
	###																###
	### This is the bootstrap for the porkchop CMS. Your webserver 	###
	### should rewrite all requests whose URI starts with /_ here	###
	### as these are designated module views.						###
	###																###
	### Copyright (C) 2014 Anthony Caravello						###
	###																###
	### This program is free software: you can redistribute it and/	###
	### or modify it under the terms of the GNU General Public		###
	### License as published by the Free Software Foundation, 		###
	### either version 3 of the License, or any later version.		###
	###																###
	### This program is distributed in the hope that it will be		###
	### useful, but WITHOUT ANY WARRANTY; without even the implied 	###
	### warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR		###
	### PURPOSE.  See the GNU General Public License for more		###
	### details.													###
	###																###
	### You should have received a copy of the GNU General Public	###
	### License along with this program.  If not, see				###
	### <http://www.gnu.org/licenses/>.								###
	###################################################################

    /**
     * PHP 7 compatability 
     *
     * Changelog:
     *  7.4.0	This function has been deprecated.
     *  5.4.0	Always returns FALSE because the magic quotes feature was removed from PHP.
     */
    # error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    if (!function_exists('get_magic_quotes_gpc')) {
        function get_magic_quotes_gpc() {
            return FALSE;
        }
    }
    
    // PHP_VERSION_ID is available as of PHP 5.2.7, if version is lower than that, then emulate it
    if (!defined('PHP_VERSION_ID')) {
        $version = explode('.', PHP_VERSION);
        define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
    }
    
    // ignore all the isset warnings for now
    if (PHP_VERSION_ID > 70000) error_reporting(~E_DEPRECATED & ~E_NOTICE);
    
	define("MODE","http");
	###################################################
	### Load Dependencies							###
	###################################################
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

	###################################################
	### Parse Request								###
	###################################################
	$_REQUEST_ = new \HTTP\Request();
	$_REQUEST_->deconstruct();
	
	# Identify Remote IP.  User X-Forwarded-For if local address
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and preg_match('/^(192\.168|172\.16|10|127\.)\./',$_SERVER['REMOTE_ADDR'])) $_REQUEST_->client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else $_REQUEST_->client_ip = $_SERVER['REMOTE_ADDR'];

	$_REQUEST_->user_agent = $_SERVER['HTTP_USER_AGENT'];

	###################################################
	### Build Dynamic Page							###
	###################################################
	# Don't Cache this Page
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");

	# Create Session
	$_SESSION_->start();
	if ($_SESSION_->error()) {
		$logger->writeln($_SESSION_->error(),'error',__FILE__,__LINE__);
		exit;
	}

	# Create Hit Record
	$_SESSION_->hit();
	if ($_SESSION_->message) $page_message = $_SESSION_->message;

	# Access Logging in Application Log
	$logger->writeln("Request from ".$_REQUEST_->client_ip." aka '".$_REQUEST_->user_agent."' Risk Score: ".$_REQUEST_->riskLevel(),'info',__FILE__,__LINE__);

	# Load Page Information
	$page = $site->page();
	$page->getPage($_REQUEST_->module,$_REQUEST_->view,$_REQUEST_->index);
	if ($page->error()) {
		print "Error: ".$page->error;
		$logger->writeln("Error initializing page: ".$page->error,'error',__FILE__,__LINE__);
		exit;
	}

	# Login-only Sites - No Public Content
	if (isset($GLOBALS['_config']->site->private) && $GLOBALS['_config']->site->private == true) $page->requireAuth();

	# Redirect old URL's
	$page->rewrite();

	# Require Terms Of Use Acceptance per page configuration
	$page->confirmTOUAcceptance();

	# Static HTML - Skip CMS Processing
	if ($page->module() == 'static') {
		// All Set!
	}
	elseif (! $page->id) {
		if (! $page->getPage('server','404')) {
			$page->module = $_REQUEST_->module;
			$page->view = $_REQUEST_->view;
			$page->index = $_REQUEST_->index;
			$page->applyStyle();
		}
	}

	// Site Counter
	$counter = new \Site\Counter("site.connections");
	$counter->increment();

	print $page->load_template();
