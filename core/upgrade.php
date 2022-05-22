<?php
	###################################################
	### upgrade.php									###
	### This module is a content management and 	###
	### and display system.							###
	### A. Caravello 11/28/2005						###
	###################################################
	### This file and its contents belong to		###
	### Root Seven Technologies.					###
	###################################################
	### Modifications								###
	### 10/4/2005	A. Caravello					###
	###		Added this header for tracking			###
	###################################################

	# Our Global Variables
	$_SESSION_ = new stdClass();

	# Don't Cache this Page
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");

	error_log("Starting upgrade script");
	error_log("\$_REQUEST: ".print_r($_REQUEST,true));
	$errorstr = '';

	$pid = getMyPid();

	# Load Configs
	require '../config/config.php';
	include(BASE."/config/upgrade.php");
	if (file_exists(BASE."/config/upgrade_local.php")) {
		include(BASE."/config/upgrade_local.php");
	}

	###################################################
	### Load API Objects							###
	###################################################
	error_log('Loading dependencies');
	
	# General Utilities
	require INCLUDES.'/functions.php';

	# Autoload Classes
	spl_autoload_register('load_class');

	$site = new \Site();

	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb-php/adodb-exceptions.inc.php';
	require THIRD_PARTY.'/adodb/adodb-php/adodb.inc.php';

	# Get version.txt
	if (file_exists(HTML."/version.txt")) {
		$ver_contents = file_get_contents(HTML."/version.txt");
		if (preg_match('/BUILD_ID\:\s(\d+)/',$ver_contents,$matches)) $site->install_log("Build: ".$matches[1],'notice');
		if (preg_match('/BUILD_DATE\:\s([\w\-\:\s]+)/',$ver_contents,$matches)) $site->install_log("Date: ".$matches[1],'notice');
	}
	else $site->install_log("version.txt not found",'warn');

	###################################################
	### Connect to Logger                           ###
	###################################################
	$logger = \Site\Logger::get_instance(array('type' => "Screen",'level' => 'info','html' => true));
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
	$site->install_log("Connecting to database ".$GLOBALS['_config']->database->master->hostname.":".$GLOBALS['_config']->database->master->port);
	
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
		$site->install_fail("Error connecting to database: ".$_database->ErrorMsg());
	}

	###################################################
	### Connect to Memcache if so configured		###
	###################################################
	$site->install_log("Connecting to ".$GLOBALS['_config']->cache->mechanism." cache");
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error) $site->install_fail('Unable to initiate Cache client: '.$_CACHE_->error);
	if ($_CACHE_->mechanism() == 'Memcache') {
		foreach ($_CACHE_->stats() as $cache_service => $cache_stats) {
			$site->install_log("Memcached host ".$cache_service." has ".$cache_stats['curr_items']." items");
		}
	}

	# Unset Templates
	$site->install_log("Clear old template settings");
	$pagelist = new \Site\PageList();
	$pages = $pagelist->find();
	foreach ($pages as $page) $page->unsetMetadata("template");

	# Upgrade Database
	$site->install_log("Upgrading Schema");
	foreach ($base_classes as $base_class => $version) {
		$class_name = "\\$base_class\\Schema";
		try {
			$class = new $class_name();
			$class_version = $class->version();
			if (! $class->upgrade()) {
				$site->install_fail("Failed to upgrade $class: ".$class->error());
			}
			$class_version = $class->version();
		} catch (Exception $e) {
			$site->install_fail("Cannot upgrade schema '".$class_name."': ".$e->getMessage());
		}
		$site->install_log("$base_class::Schema: version ".$class_version);
		if ($class_version != $version) $site->install_fail("Version $version Required");
	}

	###################################################
	### Initialize Session							###
	###################################################
	$site->install_log('Initializing Session');
	$_SESSION_ = new \Site\Session();

	###################################################
	### Get Company Information						###
	###################################################
	$companylist = new \Company\CompanyList();
	list($company) = $companylist->find();
	if (! $company->id) $site->install_fail("No company found.  You must run installer");
	$_SESSION_->company = $company;

	include(BASE."/config/upgrade.php");
	if (file_exists(BASE."/config/upgrade_local.php")) {
		include(BASE."/config/upgrade_local.php");
	}

	if ($_REQUEST['log_level']) $site->log_level = $_REQUEST['log_level'];

	$site->install_log("Starting site upgrade",'notice');
	if (file_exists(HTML."/version.txt")) {
		$site->install_log("Loaded ".HTML."/version.txt",'debug');
		$version_info = file_get_contents(HTML."/version.txt");
		if (preg_match('/PRODUCT\:\s(.+)/',$version_info,$matches)) $site->install_log("Product: ".$matches[1],'notice');
		if (preg_match('/BUILD_ID\:\s(.+)/',$version_info,$matches)) $site->install_log("Build: ".$matches[1]);
		if (preg_match('/BUILD_DATE\:\s(.+)/',$version_info,$matches)) $site->install_log("Built: ".$matches[1]);
		if (preg_match('/VERSION\:\s(.+)/',$version_info,$matches)) $site->install_log("Version: ".$matches[1],'notice');
	}
	else {
		$site->install_log("No version.txt found",'warning');
	}
	$site->loadModules($modules);
	if (! $site->setShippingLocation($company)) $site->install_log("Failed to set shipping address: ".$site->error(),'warn');
	$site->populateMenus($menus);

	$site->install_log("Upgrade completed successfully",'notice');

exit;
