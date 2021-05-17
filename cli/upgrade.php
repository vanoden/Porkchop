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

	error_log("Starting upgrade script");
	$errorstr = '';

	echo "Please provide domain name: ";
	$handle = fopen("php://stdin","r");
	$input = fgets($handle);
	$_SERVER['HTTP_HOST'] = trim ($input);
	$_SERVER['SERVER_NAME'] = trim ($input);

	# Load Config
	require '../config/config.php';

	# We'll handle errors ourselves, thank you very much
	#error_reporting(0);

	# Base Classes
	if (isset($_config->schema)) $base_classes = $_config->schema;
	else $base_classes = array(
		"Media"         => 3,
		"Product"       => 2,
		"Site"          => 6,
		"Content"       => 3,
		"Navigation"	=> 2,
		"Register"      => 19,
		"Company"       => 3,
		"Storage"       => 5,
		"Email"         => 2,
		"Package"       => 2,
		"Contact"       => 2,
	);
	//	"Support"       => 7,
	//	"Engineering"   => 6

	# Set Templates As Necessary
	if (isset($_config->templates)) $admin_templates = $_config->templates;
	else $admin_templates = array(
		array("engineering","event_report"),
		array("engineering","home"),
		array("engineering","product"),
		array("engineering","products"),
		array("engineering","project"),
		array("engineering","projects"),
		array("engineering","release"),
		array("engineering","releases"),
		array("engineering","search"),
		array("engineering","task"),
		array("engineering","tasks"),
		array("monitor","admin_assets"),
		array("monitor","admin_details"),
		array("monitor","comm_dashboard"),
		array("package","package"),
		array("package","packages"),
		array("package","versions"),
		array("product","edit"),
        array("product","report"),
		array("register","accounts"),
		array("register","admin_account"),
		array("register","organization"),
		array("register","organizations"),
		array("register","pending_customers"),
		array("spectros","admin_collections"),
		array("spectros","admin_credits"),
		array("spectros","admin_home"),
		array("spectros","cal_report"),
		array("spectros","transfer_ownership"),
		array("storage","files"),
		array("storage","repositories"),
		array("storage","repository"),
		array("support","action"),
		array("support","admin_actions"),
		array("support","register_product"),
		array("support","request_detail"),
		array("support","request_item"),
		array("support","request_items"),
		array("support","request_new"),
		array("support","requests"),
		array("alert","alert_profile"),
	);

	###################################################
	### Load API Objects							###
	###################################################
	error_log('Loading dependencies');
	
	# General Utilities
	require INCLUDES.'/functions.php';

	# Autoload Classes
	spl_autoload_register('load_class');

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
	else install_log("version.txt not found",'warn');

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
	install_log("Connecting to database ".$GLOBALS['_config']->database->master->hostname.":".$GLOBALS['_config']->database->master->port);
	
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
		install_fail("Error connecting to database: ".$_database->ErrorMsg());
	}

	###################################################
	### Connect to Memcache if so configured		###
	###################################################
	install_log("Connecting to ".$GLOBALS['_config']->cache->mechanism." cache");
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error) install_fail('Unable to initiate Cache client: '.$_CACHE_->error);
	if ($_CACHE_->mechanism() == 'Memcache') {
		list($cache_service,$cache_stats) = each($_CACHE_->stats());
		install_log("Memcached host ".$cache_service." has ".$cache_stats['curr_items']." items");
	}

	# Unset Templates
	install_log("Clear old template settings");
	$pagelist = new \Site\PageList();
	$pages = $pagelist->find();
	foreach ($pages as $page) $page->unsetMetadata("template");

	# Upgrade Database
	install_log("Upgrading Schema");
	foreach ($base_classes as $base_class => $version) {
		$class_name = "\\$base_class\\Schema";
		try {
			$class = new $class_name();
			$class_version = $class->version();
			if (! $class->upgrade()) {
				install_fail($class->error());
			}
			$class_version = $class->version();
		} catch (Exception $e) {
			install_fail("Cannot upgrade schema '".$class_name."': ".$e->getMessage());
		}
		install_log("$base_class::Schema: version ".$class_version);
		if ($class_version != $version) install_fail("Version $version Required");
	}

	###################################################
	### Initialize Session							###
	###################################################
	install_log('Initializing Session');
	$_SESSION_ = new \Site\Session();

	###################################################
	### Get Company Information						###
	###################################################
	$companylist = new \Company\CompanyList();
	list($company) = $companylist->find();
	if (! $company->id) install_fail("No company found.  You must run installer");
	$_SESSION_->company = $company;

	###################################################
	### See if Location Present						###
	###################################################
	install_log("Finding location by hostname");
	$location = new \Company\Location();
	$location->getByHost($_SERVER['SERVER_NAME']);
	if (! $location->id) {

		###################################################
		### Check Domain Information					###
		###################################################
		preg_match("/(\w+\.\w+)\$/",$_SERVER["HTTP_HOST"],$matches);
		$domain_name = $matches[1];

		install_log("Checking for domain '$domain_name'");
		$domain = new \Company\Domain();
		$domain->get($domain_name);
		if (! $domain->id) {
			install_log("Creating domain");
			# Create Domain
			$domain->add(
				array(
					'name'		=> $domain_name,
					'status'	=> 1
				)
			);
			if ($domain->error) install_fail("Failed to add domain: ".$domain->error);
		}
		else {
			install_log("Found domain ".$domain->id);
		}

		# Assign Domain to Location
		install_log("Adding location");
		$location->add(
			array(
				'company_id'	=> $_SESSION_->company->id,
				'code'			=> $_SERVER["HTTP_HOST"],
				'host'			=> $_SERVER["HTTP_HOST"],
				'domain_id'		=> $domain->id
			)
		);
		if ($location->error) install_fail("Error adding location: ".$location->error);
	}

	install_log("Add new template settings");
	foreach ($admin_templates as $array) {
		$module = $array[0];
		$view = $array[1];
		install_log("Add template 'admin.html' to $module::$view");
		$page = new \Site\Page($module,$view);
		if ($page->error) {
			install_fail("Error loading view '$view' for module '$module': ".$page->error);
		}
		if (! $page->id) {
			try {
				$page->add($module,$view,null);
			} catch (Exception $e) {
				install_fail("Cannot add view: ".$e->getMessage());
			}
			if (! $page->id) {
				install_log("Cannot find view '$view' for module '$module': ".$page->error,"warn");
				continue;
			};
		}
		$page->setMetadata("template","admin.html");
		if ($page->error) install_fail("Could not add metadata to page: ".$page->error);
	}

	# Add administrator role
	$role = new \Register\Role();
	if (! $role->get('administrator')) {
		install_log("Adding 'administrator' role");
		$role->add(array('name' => 'administrator','description' => "Access to admin tools"));
		if ($role->error) install_fail("Error adding role: ".$role->error);
	}

	install_log("Upgrade completed successfully");

	function install_log($message = '',$level = 'info') {
		print date('Y/m/d H:i:s');
		print " [$level]";
		print ": $message<br>\n";
		flush();
	}

	function install_fail($message) {
		install_log("Upgrade failed: $message",'error');
		exit;
	}
