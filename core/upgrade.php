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
		error_log("Error connecting to database: ".$_database->ErrorMsg());
		exit;
	}

	###################################################
	### Initialize Session							###
	###################################################
	error_log('Initializing Session');
	$_SESSION_ = new \Site\Session();

	###################################################
	### Get Company Information						###
	###################################################
	$companylist = new \Site\CompanyList();
	list($company) = $companylist->find();
	if (! $company->id) {
		print "No company found.  You must run installer";
		exit;
	}
	$_SESSION_->company = $company;

	###################################################
	### See if Location Present						###
	###################################################
	install_log("Finding location by hostname");
	$location = new \Site\Location();
	$location->getByHost($_SERVER['SERVER_NAME']);
	if (! $location->id) {
		###################################################
		### Check Domain Information					###
		###################################################
		preg_match("/(\w+\.\w+)\$/",$_SERVER["HTTP_HOST"],$matches);
		$domain_name = $matches[1];

		install_log("Checking for domain '$domain_name'");
		$domain = new \Site\Domain();
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
				print "Failed to add domain: ".$domain->error;
				exit;
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
			install_log("Error adding location: ".$location->error);
			exit;
		}
	}

	install_log("Upgrade completed successfully");

	function install_log($message = '',$level = 'info') {
		print date('Y/m/d H:i:s');
		print " [$level]";
		print ": $message<br>\n";
		flush();
	}
?>
