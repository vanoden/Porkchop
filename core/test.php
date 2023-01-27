<?php
	###################################################################
	### core/test.php												###
	###																###
	### This is a unit test for the porkchop CMS.					###
	###																###
	### Copyright (C) 2018 A. Caravello, RootSeven Technologies		###
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

	test_log("Site: ".$GLOBALS['_config']->site->name);
	test_log("Hostname: ".$GLOBALS['_config']->site->hostname);

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
		test_fail("Error connecting to database: ".$_database->ErrorMsg());
		exit;
	}
	test_log("Database Initiated",'trace');
	$db_info = $_database->serverInfo();
	test_log("DB Version ".$db_info['description']);

	###################################################
	### Connect to Memcache if so configured		###
	###################################################
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error) {
		test_fail('Unable to initiate Cache client: '.$_CACHE_->error);
	}

	test_log("'".$_CACHE_->mechanism()."' Cache Initiated",'trace');

	$cache_item = new \Cache\Item($_CACHE_,'_test_key');
	if ($cache_item->error) test_fail('Cache key creation failed: '.$cache_item->error);

	if ($_CACHE_->mechanism() == 'Memcache') {
		list($cache_service,$cache_stats) = each($_CACHE_->stats());
		test_log("Memcached host ".$cache_service." has ".$cache_stats['curr_items']." items");
	}

	$test_value = microtime();
	$cache_item->set($test_value);
	if ($cache_item->error) test_fail('Cache set failed: '.$cache_item->error);
	else {
		$cache_result = $cache_item->get();
		if ($cache_item->error) test_fail('Cache get failed: '.$cache_item->error);
		elseif ($cache_result == $test_value) test_log("Cached and recovered value '$cache_result' successfully");
		else (test_fail("Cache test failed: returned value '$cache_result' doesn't match"));
	}

	###################################################
	### Initialize Session							###
	###################################################
	$_SESSION_ = new \Site\Session();
	$_SESSION_->start();
	if ($_SESSION_->error) test_fail('Error starting session: '.$_SESSION_->error);
	else test_log("Session '".$_SESSION_->code."' initiated",'trace');
	if ($_SESSION_->cached()) test_log("Session already cached");

	$_SESSION_->details();
	if ($_SESSION_->cached()) test_log("Session stored in cache");
	else test_fail("Session not cached");
	test_log("<a href=\"/_admin/memcached_item?key=session[".$_SESSION_->id."]\">".$_SESSION_->code."</a>");

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

	# Access Logging in Application Log
	test_log("Request from ".$_REQUEST_->client_ip." aka '".$_REQUEST_->user_agent."'",'info');

	###################################################
	### Build Dynamic Page							###
	###################################################
	# Create Session
	$_SESSION_->start();
	if ($_SESSION_->error) {
		app_log($_SESSION_->error,'error',__FILE__,__LINE__);
		exit;
	}
	test_log('Company: '.$_SESSION_->company->name,'info');
	test_log('Domain '.$_SESSION_->domain->name,'info');

	# Create Hit Record
	$_SESSION_->hit();
	if ($_SESSION_->message) {
	    test_log("Session message: ".$_SESSION_->message);
	}

	if ($_REQUEST['login'] && $_REQUEST['password']) {
		$customer = new \Register\Customer();
		if ($_SESSION_->customer->id) {
			$customer = new \Register\Customer($_SESSION_->customer->id);
			test_log("Customer already signed in");
			if ($customer->_cached) test_log("Customer already cached");
			if ($customer->organization()->_cached) test_log("Organization already cached");
		}
		elseif ($customer->authenticate($_REQUEST['login'],$_REQUEST['password'])) {
			test_log("Customer ".$customer->code." authenticated");
			$_SESSION_->assign($customer->id);
			if ($_SESSION_->error) {
				test_fail("Unable to assign session to customer: ".$_SESSION_->error);
			}
			else {
				test_log("Session assigned to customer");
			}
			if ($customer->_cached) test_log("Customer already cached");
			if ($customer->organization()->_cached) test_log("Organization already cached");
		}
		else {
			test_fail("Customer authentication failed");
		}
	}
	# Load Page Information
	$page = new \Site\Page();
	$page->getPage('register','api');
	if ($page->error) {
		test_fail("Error initializing page: ".$page->error,'error');
	}
	test_log("Page loaded successfully");

	$product = new \Product\Item();
	$product->get($GLOBALS['_config']->spectros->calibration_product);
	if ($product->error) {
		test_fail("Error getting product: ".$product->error);
	}
	if (! $product->id) {
		test_fail("Calibration product not found");
	}

	test_log("Calibration product found");
	if ($product->_cached) {
		test_log("Product found in cache");
	}
	else test_log("Product not in cache");

	test_log("Test completed");

	function test_log($message = '',$level = 'info') {
		print date('Y/m/d H:i:s');
		print " [$level]";
		print ": $message<br>\n";
		flush();
	}

	function test_fail($message) {
		test_log("Upgrade failed: $message",'error');
		exit;
	}
?>
