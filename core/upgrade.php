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
	if (!empty($_REQUEST['log_level']) && $logger->validLevel($_REQUEST['log_level'])) $logger->level($_REQUEST['log_level']);

	$site->install_page();
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
	if ($_CACHE_->error()) $site->install_fail('Unable to initiate Cache client: '.$_CACHE_->error());
	if ($_CACHE_->mechanism() == 'Memcache') {
		foreach ($_CACHE_->stats() as $cache_service => $cache_stats) {
			if (gettype($cache_stats) == 'array') $site->install_log("Memcached host ".$cache_service." has ".$cache_stats['curr_items']." items");
			else $site->install_log("Memcached host ".$cache_service." is ".$cache_stats);
		}
	}

	# Unset Templates
	$site->install_log("Clear old template settings");
	$pagelist = new \Site\PageList();
	$pages = $pagelist->find();
	foreach ($pages as $page) $page->unsetMetadata("template");

	# Upgrade Database
	$site->install_log("Upgrading Schema");
	foreach ($modules as $class_name => $base_class) {
		$schemaClass = "\\$class_name\\Schema";
		if (! class_exists($schemaClass)) continue;
		if (! key_exists('schema',$base_class)) {
			$site->install_log("No schema requirement for $class_name",'warning');
			$requiredVersion = null;
		}
		else $requiredVersion = $base_class['schema'];
		try {
			$class = new $schemaClass();
			$class_version = $class->version();
			if (! $class->upgrade()) {
				$site->install_fail("Failed to upgrade $class: ".$class->error());
			}
			$class_version = $class->version();
		} catch (Exception $e) {
			$site->install_fail("Cannot upgrade schema '".$class_name."': ".$e->getMessage());
		}
		$site->install_log("$class_name::Schema: version ".$class_version);
		if (!empty($requiredVersion) && $class_version != $requiredVersion) $site->install_fail("Version $requiredVersion Required");
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
	$site->install_log("Company: ".$company->name());
	$_SESSION_->company = $company;

	// Get Location
	$location = new \Company\Location();
	$location->get($_SERVER['SERVER_NAME']);
	if (! $location->id) {
		$site->install_log("Location ".$_SERVER['SERVER_NAME']." not configured.  Adding to company ".$company->id);
		$location->add(array('company_id' => $company->id, 'code' => $_SERVER['SERVER_NAME']));
		if (! $location->id) $site->install_fail("Error adding location");
		else $site->install_log("Added location: ".$location->name." [".$location->id."]");
	}

	// Get Domain
	$domain = new \Company\Domain($location->domain_id);
	if (! $domain->exists()) {
		$domain = new \Company\Domain();
		$domain->get($_SERVER['SERVER_NAME']);
		if (! $domain->id) {
			$site->install_log("Domain ".$_SERVER['SERVER_NAME']." not configured.  Adding to location ".$location->id);
			$domain->add(array('location_id' => $location->id, 'name' => $_SERVER['SERVER_NAME']));
			if (! $domain->id) $site->install_fail("Error adding domain");
			else $site->install_log("Added domain: ".$domain->name()." [".$domain->id."]");
		}
		else $site->install_log("Domain: ".$domain->name);
		if ($location->domain_id != $domain->id) {
			$location->update(array('domain_id' => $domain->id()));
			$site->install_log("Updated location ".$location->name." with domain ".$domain->name);
		}
	}
	else {
		$site->install_log("Domain: ".$domain->id()." found");
	}

	include(BASE."/config/upgrade.php");
	if (file_exists(BASE."/config/upgrade_local.php")) {
		include(BASE."/config/upgrade_local.php");
	}

	if (!empty($_REQUEST['log_level'])) $site->log_level($_REQUEST['log_level']);

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

	$site->install_log("Load Modules");
	$site->loadModules($modules);

	$site->install_log("Populate Menus");
	$site->populateMenus($menus);

	$site->install_log("Upgrade completed successfully",'notice');

exit;
