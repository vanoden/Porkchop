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
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error) {
		install_fail('Unable to initiate Cache client: '.$_CACHE_->error);
	}
	if ($_CACHE_->mechanism() == 'Memcache') {
		list($cache_service,$cache_stats) = each($_CACHE_->stats());
		install_log("Memcached host ".$cache_service." has ".$cache_stats['curr_items']." items");
	}

	# Upgrade Database
	$class = new \Product\Schema();
	install_log("Product::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Support\Schema();
	install_log("Support::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Content\Schema();
	install_log("Content::Schema: version ".$class->version());
	if ($class->version() != 3) install_fail("Version 3 Required");
	$class = new \Company\Schema();
	install_log("Company::Schema: version ".$class->version());
	if ($class->version() != 3) install_fail("Version 3 Required");
	$class = new \Site\Schema();
	install_log("Session::Schema: version ".$class->version());
	if ($class->version() != 4) install_fail("Version 4 Required");
	$class = new \Email\Schema();
	install_log("Email::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Spectros\Schema();
	install_log("Spectros::Schema: version ".$class->version());
	if ($class->version() != 5) install_fail("Version 5 Required");
	$class = new \Action\Schema();
	install_log("Action::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Register\Schema();
	install_log("Register::Schema: version ".$class->version());
	if ($class->version() != 10) install_fail("Version 10 Required");
	$class = new \Storage\Schema();
	install_log("Storage::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Package\Schema();
	install_log("Package::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Monitor\Schema();
	install_log("Monitor::Schema: version ".$class->version());
	if ($class->version() != 18) install_fail("Version 18 Required");
	$class = new \Media\Schema();
	install_log("Media::Schema: version ".$class->version());
	if ($class->version() != 3) install_fail("Version 3 Required");
	$class = new \Contact\Schema();
	install_log("Contact::Schema: version ".$class->version());
	if ($class->version() != 2) install_fail("Version 2 Required");
	$class = new \Event\Schema();
#	install_log("Event::Schema: version ".$class->version());
#	if ($class->version() != 0) install_fail("Version 1 Required");
	$class = new \Engineering\Schema();
	install_log("Engineering::Schema: version ".$class->version());
	if ($class->version() != 3) install_fail("Version 3 Required");

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

	# Set Templates As Necessary
	$set_template_array = array(
		array("spectros","admin_home"),
		array("product","report"),
		array("product","edit"),
		array("register","organizations"),
		array("register","organization"),
		array("register","accounts"),
		array("monitor","admin_assets"),
		array("monitor","admin_details"),
		array("monitor","admin_collections"),
		array("spectros","admin_credits"),
		array("spectros","cal_report"),
		array("monitor","comm_dashboard"),
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
	);

	install_log("Add new template settings");
	foreach ($set_template_array as $array) {
		install_log("Add template 'admin.html' to $module::$view");
		$module = $array[0];
		$view = $array[1];
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
	$role->add(array('name' => 'administrator','description' => "Access to admin tools"));
	if ($role->error) install_fail("Error adding role: ".$role->error);

	# Check for Calibration Credit Product
	install_log("Check Calibration Verification Product");
	if (isset($GLOBALS['_config']->spectros->calibration_product) and strlen($GLOBALS['_config']->spectros->calibration_product)) {
		$product = new \Product\Item();
		$product->get($GLOBALS['_config']->spectros->calibration_product);
		if (! $product->id) {
			install_fail("No Calibration Verification Credit product found, code '".$GLOBALS['_config']->spectros->calibration_product."' must exist.");
		}
		install_log("Product '".$GLOBALS['_config']->spectros->calibration_product."' found.");
	}
	else {
		install_fail("_config->spectros->calibration_product not defined!");
	}

	if (isset($GLOBALS['_config']->monitor->default_sensor_product) and strlen($GLOBALS['_config']->monitor->default_sensor_product)) {
		$product = new \Product\Item();
		$product->get($GLOBALS['_config']->monitor->default_sensor_product);
		if (! $product->id) {
			install_fail("No Generic Sensor product found, code '".$GLOBALS['_config']->monitor->default_sensor_product."' must exist.");
		}
		install_log("Product '".$GLOBALS['_config']->monitor->default_sensor_product."' found.");
	}
	else {
		install_fail("_config->monitor->default_sensor_product not defined!");
	}

	# Check for Dashboards
	install_log("Check default dashboard");
	$dashboard = new \Monitor\Dashboard();
	$dashboard->get('default');
	if (! $dashboard->id) {
		install_log("Adding default dashboard");
		$dashboard->add(array("name" => 'default','template' => '/dashboards/default.html'));
		if ($dashboard->error) install_fail("Cannot find or add default dashboard: ".$dashboard->error);
	}
	install_log("Check concept dashboard");
	$dashboard = new \Monitor\Dashboard();
	$dashboard->get('concept');
	if (! $dashboard->id) {
		install_log("Adding concept dashboard");
		$dashboard->add(array("name" => 'concept','template' => '/dashboards/concept/concept.html'));
		if ($dashboard->error) install_fail("Cannot find or add default dashboard: ".$dashboard->error);
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
