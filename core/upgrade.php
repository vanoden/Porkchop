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

	# Upgrade Database
	$class = new \Product\Schema();
	install_log("Product::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Support\Schema();
	install_log("Support::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Content\Schema();
	install_log("Content::Schema: version ".$class->version());
	if ($class->version() != 3) install_fail("Version 1 Required");
	$class = new \Company\Schema();
	install_log("Company::Schema: version ".$class->version());
	if ($class->version() != 2) install_fail("Version 1 Required");
	$class = new \Session\Schema();
	install_log("SEssion::Schema: version ".$class->version());
	if ($class->version() != 4) install_fail("Version 1 Required");
	$class = new \Email\Schema();
	install_log("Email::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Spectros\Schema();
	install_log("Spectros::Schema: version ".$class->version());
	if ($class->version() != 5) install_fail("Version 1 Required");
	$class = new \Action\Schema();
	install_log("Action::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Register\Schema();
	install_log("Register::Schema: version ".$class->version());
	if ($class->version() != 10) install_fail("Version 1 Required");
	$class = new \Storage\Schema();
	install_log("Storage::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Package\Schema();
	install_log("Package::Schema: version ".$class->version());
	if ($class->version() != 1) install_fail("Version 1 Required");
	$class = new \Monitor\Schema();
	install_log("Monitor::Schema: version ".$class->version());
	if ($class->version() != 14) install_fail("Version 1 Required");
	$class = new \Media\Schema();
	install_log("Media::Schema: version ".$class->version());
	if ($class->version() != 3) install_fail("Version 1 Required");
	$class = new \Contact\Schema();
	install_log("Contact::Schema: version ".$class->version());
	if ($class->version() != 2) install_fail("Version 1 Required");
	$class = new \Event\Schema();
	install_log("Event::Schema: version ".$class->version());
	if ($class->version() != 0) install_fail("Version 1 Required");

	###################################################
	### Initialize Session							###
	###################################################
	install_log('Initializing Session');
	$_SESSION_ = new \Session\Session();

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
	$pagelist = \Site\PageList->new();
	$pages = $pagelist->find();
	foreach my ($pages as $page) {
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
	);

	foreach my ($set_template_array as $module => $view) {
		$page = new \Site\Page($module,$view);
		$page->setMetadata("template","admin.html");
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
