<?PHP	
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
		app_log("Error connecting to database: ".$_database->ErrorMsg(),'error',__FILE__,__LINE__);
		exit;
	}
	app_log("Database Initiated",'trace',__FILE__,__LINE__);

	###################################################
	### Connect to Memcache if so configured		###
	###################################################
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error) {
		test_fail('Unable to initiate Cache client: '.$_CACHE_->error);
	}
	app_log("Cache Initiated",'trace',__FILE__,__LINE__);

	###################################################
	### Initialize Session							###
	###################################################
	$_SESSION_ = new \Site\Session();
	$_SESSION_->start();
	app_log("Session initiated",'trace',__FILE__,__LINE__);

	###################################################
	### Parse Request								###
	###################################################
	$_REQUEST_ = new \HTTP\Request();
	$_REQUEST_->deconstruct();

	# Identify Remote IP.  User X-Forwarded-For if local address
	if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) and preg_match('/^(192\.168|172\.16|10|127\.)\./',$_SERVER['REMOTE_ADDR'])) $_REQUEST_->client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else $_REQUEST_->client_ip = $_SERVER['REMOTE_ADDR'];

	$_REQUEST_->user_agent = $_SERVER['HTTP_USER_AGENT'];
	$_REQUEST_->timer = microtime();

	###################################################
	### Build Dynamic Page							###
	###################################################
	# Don't Cache this Page
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");

	# Create Session
	$_SESSION_->start();
	if ($_SESSION_->error) {
		app_log($_SESSION_->error,'error',__FILE__,__LINE__);
		exit;
	}

	# Create Hit Record
	$_SESSION_->hit();
	if ($_SESSION_->message) {
	    $page_message = $_SESSION_->message;
	}

	# Access Logging in Application Log
	app_log("Request from ".$_REQUEST_->client_ip." aka '".$_REQUEST_->user_agent."'",'info',__FILE__,__LINE__);

	# Load Page Information
	$_page = new \Site\Page();
	$_page->get($_REQUEST_->module,$_REQUEST_->view,$_REQUEST_->index);
	if ($_page->error) {
		print "Error: ".$_page->error;
		app_log("Error initializing page: ".$_page->error,'error',__FILE__,__LINE__);
		exit;
	}
	print $_page->load_template();
?>
