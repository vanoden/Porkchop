<?php
	###################################################
	### install.php									###
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

	error_log("Starting install script");
	error_log("\$_REQUEST: ".print_r($_REQUEST,true));
	$errorstr = '';

	$pid = getMyPid();

	# Load Config
	require '../config/config.php';

	# We'll handle errors ourselves, thank you very much
	ini_set('display_errors','1');
	ini_set('display_startup_errors','1');
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

	###################################################
	### Load API Objects							###
	###################################################
	error_log('Loading dependencies');
	
	# General Utilities
	require INCLUDES.'/functions.php';

	# Autoload Classes
	spl_autoload_register('load_class');

	$site = new \Site();
	if (isset($_REQUEST['log_level'])) $site->log_level($_REQUEST['log_level']);

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
	$logger = \Site\Logger::get_instance(array('type' => "Screen",'level' => $site->log_level(),'html' => true));
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
	### Check Input									###
	###################################################
	if (isset($_REQUEST['submit'])) {
		if ($_REQUEST['password_1'] != $_REQUEST['password_2'])
			$errorstr .= "Passwords Don't Match!<br>";
		if (! $_REQUEST['company_name'])
			$errorstr .= "Company Name Required!<br>";
		if (! $_REQUEST['password_1'])
			$errorstr .= "Password Required";
	}

	preg_match("/(\w[\w\-\.]+)\$/",$_SERVER["HTTP_HOST"],$matches);
	$domain_name = $matches[1];

	###################################################
	### Ask a few questions							###
	###################################################
	if ((! isset($_REQUEST['submit'])) or ($errorstr))	{
	if (! isset($_REQUEST['company_name'])) $_REQUEST['company_name'] = "";
	if (! isset($_REQUEST['admin_login'])) $_REQUEST['admin_login'] = "admin";
?>
<html>
<head>
	<style>
		table {
			width: 400px;
			border: 1px solid black;
		}
		th {
			width: 200px;
		}
		td.error {
			border: 1px solid red;
			width: 100%;
			background-color: pink;
			color: red;
		}
	</style>
</head>
<body>
<form method="post" action="_install">
<table>
<tr><th colspan="2">Porchop Web Installer V2.0</th></tr>
</table>
<?php if ($errorstr) print "<table><tr><td colspan=\"2\" class=\"error\">There are errors in your submittal:<br>$errorstr</td></tr></table>";?>
<table>
<tr><th>Company Name</th><td><input type="text" name="company_name" value="<?=$_REQUEST['company_name']?>"/></td></tr>
</table>
<table>
<tr><th>Admin Login</th><td><input type="text" name="admin_login" value="<?=$_REQUEST['admin_login']?>"/></td></tr>
<tr><th>Password</th><td><input type="password" name="password_1" value=""/></td></tr>
<tr><th>Confirm</th><td><input type="password" name="password_2" value=""/></td></tr>
</table>
<table>
<tr><th>Maintenance Mode?</th><td><input type="radio" name="status" value="1"/>No &nbsp; <input type="radio" name="status" value="0"/>Yes</td></tr>
</table>
<table>
<tr><th colspan="2"><input type="submit" name="submit" value="Submit"/></th></tr>
</table>
</form>
</body>
</html>
<?php
		exit;
	}

	###################################################
	### Initialize Common Objects					###
	###################################################
	//print "Porkchop CMS Installation Log<br>";
	//print "<table><tr><th>Time</th><th>Process</th><th>Level</th><th>Message</th></tr>";
	$site->install_log("Porkchop CMS Install Starting");
	$site->install_log("Connecting to database server");

	# Connect to Database
	$_database = NewADOConnection($GLOBALS['_config']->database->driver);
	$_database->port = $GLOBALS['_config']->database->master->port;
	$connect_success = $_database->Connect(
		$GLOBALS['_config']->database->master->hostname,
		$GLOBALS['_config']->database->master->username,
		$GLOBALS['_config']->database->master->password
	);
	if (! $connect_success) {
		$site->install_log("Connection failed",'error');
		exit;
	}
	$site->install_log("Connection successful");

	###################################################
	### Connect to Memcache if so configured                ###
	###################################################
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error()) {
		$site->install_log('Unable to initiate Cache client: '.$_CACHE_->error(),'error');
	}
	else {
		$site->install_log("Cache Initiated");
	}

	# Check For Existing Database
	$site->install_log("Checking for existing schema");
	$_database->Execute("use ".$GLOBALS['_config']->database->schema);
	if ($_database->ErrorMsg()) {
		$site->install_log("Schema ".$GLOBALS['_config']->database->schema." not found. Creating");
		$_database->Execute("CREATE DATABASE ".$GLOBALS['_config']->database->schema.";");
		if ($_database->ErrorMsg()) {
			$site->install_log("Error creating database: ".$_database->ErrorMsg(),'error');
			exit;
		}
		$_database->Execute("use ".$GLOBALS['_config']->database->schema);
	}

	###################################################
	### Start Session								###
	###################################################
	$_SESSION_ = new \Site\Session;
	$_SESSION_->elevate();

	$site->install_log("Creating Company Schema");
	$company_schema = new \Company\Schema();
	if (! $company_schema->upgrade()) {
		install_log("Error creating Company schema: ".$company_schema->error());
		exit;
	}
	$site->install_log("Creating Session Schema");
	$session_schema = new \Site\Schema();
	if (! $session_schema->upgrade()) {
		install_log("Error creating Site schema: ".$site_schema->error());
		exit;
	}
	$geography_schema = new \Geography\Schema();
	if (! $geography_schema->upgrade()) {
		$site->install_log("Error creating Geography schema: ".$geography_schema->error());
		exit;
	}
	$register_schema = new \Register\Schema();
	if (! $register_schema->upgrade()) {
		$site->install_log("Error creating Register schema: ".$register_schema->error());
		exit;
	}

	###################################################
	### Check Install Status...no over-installs		###
	###################################################
	$site_config = new \Site\Configuration;
	if ($site_config->get("_install_complete")) {
		$site->install_log("Installation already completed");
		exit;
	}

	###################################################
	### Initialize Session							###
	###################################################
	$site->install_log("Starting session");
	$_SESSION_ = new \Site\Session();

	###################################################
	### Get Company Information						###
	###################################################
	$site->install_log("Setting up company");
	$company = new \Company\Company();
	if ($company->error()) {
		$site->install_log("Error loading company module: ".$company->error(),'error');
		exit;
	}
	$site->install_log("Checking for existing company");
	$company->get($_REQUEST['company_name']);

	if (! $company->id) {
		$site->install_log("Adding company");
		$company->add(
			array(
				"name" => $_REQUEST['company_name'],
			)
		);
		if ($company->error()) {
			install_log("Cannot add company: ".$company->error());
			exit;
		}
	}
	else {
		$site->install_log("Company already present");
	}
	$GLOBALS['_SESSION_']->company = $company->id;

	$site->install_log("Setting up domain");
	$domain = new \Company\Domain();
	$domain->get($domain_name);

	if (! $domain->id) {
		$site->install_log("Adding domain");
		$domain->add(
			array(
				"active"		=> 1,
				"status"		=> $_REQUEST["status"],
				"name"			=> $domain_name,
				"company_id"	=> $company->id,
			)
		);
		if ($domain->error()) {
			$site->install_log("Cannot add domain: ".$domain->error());
			exit;
		}
	}
	else {
		$site->install_log("Domain already present");
	}

	$site->install_log("Setting up Location");
	$location = new \Company\Location();
	$location->getByHost($_SERVER['SERVER_NAME']);
	if ($location->id) {
		$site->install_log("Location Located");
	}
	else {
		$location->add(array(
				"name"	=> $_SERVER['SERVER_NAME'],
				"host"	=> $_SERVER['SERVER_NAME'],
				"domain_id" => $domain->id,
				"company_id" => $company->id,
				"code"	=> uniqid()
			)
		);
		if ($location->error()) {
			$site->install_log("Failed to add location: ".$location->error(),'error');
			exit;
		}
	}

	$site->install_log("Adding default organization '".$_REQUEST['company_name']."'");
	$organization = new \Register\Organization();
	if ($organization->get($_REQUEST['company_name'])) {
		$site->install_log("Organization already present");
	}
	elseif ($organization->add(array('name' => $_REQUEST['company_name']))) {
		$site->install_log("Created Organization ".$organization->id);
	}
	else {
		$site->install_fail("Error adding default organization: ".$organization->error());
	}

	$site->install_log("Setting up admin account");
	$admin = new \Register\Customer();
	if ($admin->error()) {
		$site->install_log("Error initializing Admin object: ".$admin->error(),'error');
		exit;
	}
	$admin->get($_REQUEST['admin_login']);
	if ($admin->error()) {
		$site->install_log("Error identifying superuser: ".$admin->error(),'error');
		exit;
	}

	if (! $admin->id) {
		$site->install_log("Adding admin account");
		$admin->add(
			array(
				"login"			=> $_REQUEST['admin_login'],
				"password"		=> $_REQUEST['password_1'],
				"company_id"	=> $company->id,
				"status"		=> 'active',
				"organization_id"	=> $organization->id
			)
		);
		if ($admin->error()) {
			install_log("Cannot add admin user: ".$admin->error(),'error');
			exit;
		}
	}
	else {
		$site->install_log("Admin already exists");
	}

	# Must Grant Privileges to set up roles
	$site->install_log("Elevating privileges for install");
	$_SESSION_->elevate();

	# Get Existing Roles
	$site->install_log("Getting available roles");
	$rolelist = new \Register\RoleList();
	$roles = $rolelist->find();
	if ($rolelist->error()) {
		$site->install_log("Error getting roles: ".$rolelist->error(),'error');
		exit;
	}

	$site->install_log("Granting roles");
	foreach ($roles as $role) {		
		if ($admin->has_role($role->name)) {
			$site->install_log("Already has role ".$role->name);
			continue;
		}
		$site->install_log("Granting ".$role->name."[".$role->id."]");
		$admin->add_role($role->id);
		if ($admin->error()) {
			error_log("Error: ".$admin->error());
			$site->install_log("Error: ".$admin->error(),'error');
			exit;
		}
	}

	$site_config->set("_install_complete",1);

	$site->install_log("Installation completed successfully");

?>
