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

	# Command Line Parameters
	$_REQUEST = array(
		"log_level"	=> "info"
	);

	foreach ($argv as $argument) {
		if (preg_match('/^\-\-([\w\-]+)\=(.*)$/',$argument,$matches)) {
			$key = $matches[1];
			$value = $matches[2];
			preg_replace('/\-/','_',$key);
			$_REQUEST[$key] = $value;
		}
	}

	# Our Global Variables
	$_SESSION_ = new stdClass();

	error_log("Starting upgrade script");
	$errorstr = '';

	if (empty($_REQUEST['domain'])) {
		echo "Please provide domain name: ";
		$handle = fopen("php://stdin","r");
		$input = fgets($handle);
		$_SERVER['HTTP_HOST'] = trim ($input);
		$_SERVER['SERVER_NAME'] = trim ($input);
	}
	else {
		$_SERVER['HTTP_HOST'] = $_REQUEST['domain'];
		$_SERVER['SERVER_NAME'] = $_REQUEST['domain'];
	}

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

	# Don't Cache this Page
	#header("Expires: 0");
	#header("Cache-Control: no-cache, must-revalidate");

	# Get version.txt
	if (file_exists(HTML."/version.txt")) {
		$ver_contents = file_get_contents(HTML."/version.txt");
		if (preg_match('/BUILD_ID\:\s(\d+)/',$ver_contents,$matches)) install_log("Build: ".$matches[1],'notice');
		if (preg_match('/BUILD_DATE\:\s([\w\-\:\s]+)/',$ver_contents,$matches)) install_log("Date: ".$matches[1],'notice');
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

    $_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
    if ($_CACHE_->error()) {
        $site->install_log('Unable to initiate Cache client: '.$_CACHE_->error(),'error');
    }
    $site->install_log("Cache Initiated");

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

	# Update Schemas 
	$site->loadModules($modules);

	###################################################
	### See if Location Present						###
	###################################################
	$site->install_log("Finding location by hostname");
	$location = new \Company\Location();
	$location->getByHost($_SERVER['SERVER_NAME']);
	if (! $location->id) {
		###################################################
		### Check Domain Information					###
		###################################################
		preg_match("/(\w+\.\w+)\$/",$_SERVER["HTTP_HOST"],$matches);
		$domain_name = $matches[1];

		$site->install_log("Checking for domain '$domain_name'");
		$domain = new \Company\Domain();
		$domain->get($domain_name);
		if (! $domain->id) {
			$site->install_log("Creating domain");
			# Create Domain
			$domain->add(
				array(
					'name'		=> $domain_name,
					'status'	=> 1
				)
			);
			if ($domain->error()) $this->install_fail("Failed to add domain: ".$domain->error());
		}
		else {
			$site->install_log("Found domain ".$domain->id);
		}

		# Assign Domain to Location
		$site->install_log("Adding location");
		$location->add(
			array(
				'company_id'	=> $_SESSION_->company->id,
				'code'			=> $_SERVER["HTTP_HOST"],
				'host'			=> $_SERVER["HTTP_HOST"],
				'domain_id'		=> $domain->id
			)
		);
		if ($location->error()) install_fail("Error adding location: ".$location->error());
	}

	# Add administrator role
	$role = new \Register\Role();
	if (! $role->get('administrator')) {
		$site->install_log("Adding 'administrator' role");
		$role->add(array('name' => 'administrator','description' => "Access to admin tools"));
		if ($role->error()) install_fail("Error adding role: ".$role->error());
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

	if (false) {
		# Unset Templates
		$site->install_log("Clear old template settings");
		$pagelist = new \Site\PageList();
		$pages = $pagelist->find();
		foreach ($pages as $page) $page->unsetMetadata("template");
	}

	#$site->setShippingLocation($company);
	$site->populateMenus($menus);

	$site->install_log("Upgrade completed successfully",'notice');

exit;
