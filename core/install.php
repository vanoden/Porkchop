<?
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

	error_log("Starting install script");
	error_log("\$_REQUEST: ".print_r($_REQUEST,true));
	$errorstr = '';

	# Load Config
	require '../config/config.php';

	# We'll handle errors ourselves, thank you very much
	#error_reporting(0);

	###################################################
	### Load API Objects							###
	###################################################
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
	### Check Input									###
	###################################################
	if ($_REQUEST['submit']) {
		if ($_REQUEST['password_1'] != $_REQUEST['password_2'])
			$errorstr .= "Passwords Don't Match!<br>";
		if (! $_REQUEST['company_name'])
			$errorstr .= "Company Name Required!<br>";
		if (! $_REQUEST['password_1'])
			$errorstr .= "Password Required";
	}

	preg_match("/(\w+\.\w+)\$/",$_SERVER["HTTP_HOST"],$matches);
	$domain_name = $matches[1];

	###################################################
	### Ask a few questions							###
	###################################################
	if ((! $_REQUEST['submit']) or ($errorstr))	{
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
<? if ($errorstr) print "<table><tr><td colspan=\"2\" class=\"error\">There are errors in your submittal:<br>$errorstr</td></tr></table>";?>
<table>
<tr><th>Company Name</th><td><input type="text" name="company_name" value="<?=$_REQUEST['company_name']?>"/></td></tr>
</table>
<table>
<tr><th>Admin Login</th><td><input type="text" name="admin_login" value="<?=$_REQUEST['admin_login']?>"/></td></tr>
<tr><th>Password</th><td><input type="password" name="password_1" value=""/></td></tr>
<tr><th>Confirm</th><td><input type="password" name="password_2" value=""/></td></tr>
</table>
<table>
<tr><th>Database</th><td><input type="text" name="schema" value="<?=$_REQUEST['schema']?>"/></td></tr>
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
<?
		exit;
	}

	###################################################
	### Initialize Common Objects					###
	###################################################
	install_log("Porkchop CMS Install Starting");
	install_log("Connecting to database server");
	# Connect to Database
	$_database = NewADOConnection($GLOBALS['_config']->database->driver);
	$_database->port = $GLOBALS['_config']->database->master->port;
	$connect_success = $_database->Connect(
		$GLOBALS['_config']->database->master->hostname,
		$GLOBALS['_config']->database->master->username,
		$GLOBALS['_config']->database->master->password
	);
	if (! $connect_success) {
		install_log("Connection failed",'error');
		exit;
	}
	install_log("Connection successful");

	###################################################
	### Connect to Memcache if so configured                ###
	###################################################
	$_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
	if ($_CACHE_->error) {
		install_log('Unable to initiate Cache client: '.$_CACHE_->error,'error');
	}
	install_log("Cache Initiated");

	# Check For Existing Database
	install_log("Checking for existing schema");
	$_database->Execute("use ".$GLOBALS['_config']->database->schema);
	if ($_database->ErrorMsg()) {
		install_log("Schema ".$GLOBALS['_config']->database->schema." not found. Creating");
		$_database->Execute("CREATE DATABASE ".$GLOBALS['_config']->database->schema.";");
		if ($_database->ErrorMsg()) {
			install_log("Error creating database: ".$_database->ErrorMsg(),'error');
			exit;
		}
		$_database->Execute("use ".$GLOBALS['_config']->database->schema);
	}

	install_log("Creating Schema");
	$company_schema = new \Company\Schema();
	$session_schema = new \Site\Schema();

	install_log("Starting session");
	$_SESSION_ = new \Site\Session();

	# See if Company Exists
	install_log("Setting up company");
	$company = new \Company\Company();
	if ($company->error) {
		install_log("Error loading company module: ".$company->error,'error');
		exit;
	}
	install_log("Checking for existing company");
	$company->get($_REQUEST['company_name']);

	if (! $company->id) {
		install_log("Adding company");
		$company->add(
			array(
				"name" => $_REQUEST['company_name'],
			)
		);
		if ($company->error) {
			install_log("Cannot add company: ".$company->error);
			exit;
		}
	}
	else {
		install_log("Company already present");
	}
	$GLOBALS['_SESSION_']->company = $company->id;

	install_log("Setting up domain");
	$domain = new \Company\Domain();
	$domain->get($domain_name);

	if (! $domain->id) {
		install_log("Adding domain");
		$domain->add(
			array(
				"active"		=> 1,
				"status"		=> $_REQUEST["status"],
				"name"			=> $domain_name,
				"company_id"	=> $company->id,
			)
		);
		if ($domain->error) {
			install_log("Cannot add domain: ".$domain->error);
			exit;
		}
	}
	else {
		install_log("Domain already present");
	}

	install_log("Setting up Location");
	$location = new \Company\Location();
	$location->getByHost($_SERVER['SERVER_NAME']);
	if ($location->id) {
		install_log("Location Located");
	}
	else {
		$location->add(array(
				name	=> $_SERVER['SERVER_NAME'],
				host	=> $_SERVER['SERVER_NAME'],
				domain_id => $domain->id,
				company_id => $company->id,
				code	=> uniqid()
			)
		);
		if ($location->error) {
			install_log("Failed to add location: ".$location->error,'error');
			exit;
		}
	}

	install_log("Setting up admin account");
	$admin = new \Register\Customer();
	if ($admin->error) {
		install_log("Error initializing Admin object: ".$admin->error,'error');
		exit;
	}
	$admin->get($_REQUEST['admin_login']);
	if ($admin->error) {
		install_log("Error identifying superuser: ".$admin->error,'error');
		exit;
	}

	if (! $admin->id) {
		install_log("Adding admin account");
		$admin->add(
			array(
				"login"			=> $_REQUEST['admin_login'],
				"password"		=> $_REQUEST['password_1'],
				"company_id"	=> $company->id,
				"status"		=> 'active',
			)
		);
		if ($admin->error) {
			install_log("Cannot add admin user: ".$admin->error,'error');
			exit;
		}
	}
	else {
		install_log("Admin already exists");
	}

	# Must Grant Privileges to set up roles
	install_log("Elevating privileges for install");
	$_SESSION_->elevate();

	# Get Existing Roles
	install_log("Getting available roles");
	$rolelist = new \Register\RoleList();
	$roles = $rolelist->find();
	if ($rolelist->error) {
		install_log("Error getting roles: ".$rolelist->error,'error');
		exit;
	}

	install_log("Granting roles");
	foreach ($roles as $role) {		
		if ($admin->has_role($role->code)) {
			install_log("Already has role ".$role->code);
			continue;
		}
		install_log("Granting ".$role->name."[".$role->id."]");
		$admin->add_role($role->id);
		if ($admin->error) {
			error_log("Error: ".$admin->error);
			install_log("Error: ".$admin->error,'error');
			exit;
		}
	}

	install_log("Installation completed successfully");

	function install_log($message = '',$level = 'info') {
		print date('Y/m/d H:i:s');
		print " [$level]";
		print ": $message<br>\n";
		flush();
	}
?>
