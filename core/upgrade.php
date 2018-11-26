<?
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
	error_log("\$_REQUEST: ".print_r($_REQUEST,true));
	$errorstr = '';

	# Load Config
	require '../config/config.php';

	# We'll handle errors ourselves, thank you very much
	#error_reporting(0);

	# Base Classes
	if (isset($_config->schema)) $base_classes = $_config->schema;
	else $base_classes = array(
        "Media"     => 3,
        "Product"   => 1,
        "Site"      => 5,
        "Content"   => 3,
        "Register"  => 10,
        "Company"   => 3,
        "Storage"   => 1,
        "Email"     => 1,
        "Package"   => 1,
        "Contact"   => 2,
        "Support"   => 2,
        "Engineering"   => 3,
    );

	# Set Templates As Necessary
	$admin_templates = array(
		array("product","report"),
		array("product","edit"),
		array("register","organizations"),
		array("register","organization"),
		array("register","accounts"),
		array("register","admin_account"),
		array("engineering","home"),
		array("engineering","tasks"),
		array("engineering","task"),
		array("engineering","releases"),
		array("engineering","release"),
		array("engineering","products"),
		array("engineering","product"),
		array("engineering","projects"),
		array("engineering","project"),
		array("engineering","event_report"),
		array("engineering","search"),
		array("support","request_new"),
		array("support","requests"),
		array("support","request_detail"),
		array("support","request_item"),
		array("support","action"),
		array("support","admin_actions")
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
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");

	# Get version.txt
	if (file_exists(HTML."/version.txt")) {
		$ver_contents = file_get_contents(HTML."/version.txt");
		if (preg_match('/BUILD_ID\:\s(\d+)/',$ver_contents,$matches)) {
			install_log("Build: ".$matches[1],'notice');
		}
		if (preg_match('/BUILD_DATE\:\s([\w\-\:\s]+)/',$ver_contents,$matches)) {
			install_log("Date: ".$matches[1],'notice');
		}
	}
	else install_log("version.txt not found",'warn');

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
	install_log("Connecting to ".$GLOBALS['_config']->Cache->mechanism." cache");
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error) {
		install_fail('Unable to initiate Cache client: '.$_CACHE_->error);
	}
	if ($_CACHE_->mechanism() == 'Memcache') {
		list($cache_service,$cache_stats) = each($_CACHE_->stats());
		install_log("Memcached host ".$cache_service." has ".$cache_stats['curr_items']." items");
	}

	# Upgrade Database
	install_log("Upgrading Schema");
	foreach ($base_classes as $base_class => $version) {
		$class_name = "\\$base_class\\Schema";
		try {
			$class = new $class_name();
			$class_version = $class->version();
		} catch (Exception $e) {
			install_fail("Cannot upgrade schema: ".$e->getMessage());
		}
		install_log("$base_class::Schema: version ".$class_version);
		if ($class_version != $version) {
			install_fail("Version $version Required");
		}
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
	if (! $company->id) {
		install_fail("No company found.  You must run installer");
	}
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
			if ($domain->error) {
				install_fail("Failed to add domain: ".$domain->error);
			}
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
		if ($location->error) {
			install_fail("Error adding location: ".$location->error);
		}
	}

	# Unset Templates
	install_log("Clear old template settings");
	$pagelist = new \Site\PageList();
	$pages = $pagelist->find();
	foreach ($pages as $page) {
		$page->unsetMetadata("template");
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
		if ($page->error) {
			install_fail("Could not add metadata to page: ".$page->error);
		}
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
?>
