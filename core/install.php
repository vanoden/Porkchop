<?PHP	
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

	#error_log("###### Page: ".$_SERVER["REQUEST_URI"]."######");
	#error_log("\$_REQUEST: ".print_r($_REQUEST,true));
	$errorstr = '';

	# Load Config
	require '../config/config.php';

	# We'll handle errors ourselves, thank you very much
	error_reporting(0);

	###################################################
	### Load API Objects							###
	###################################################
	# General Utilities
	require INCLUDES.'/functions.php';

	# Company Class
	require MODULES.'/company/_classes/default.php';

	# Register Class - Customers, Admins and Authentication
	require MODULES.'/register/_classes/default.php';

	# Session Classes
	require MODULES.'/session/_classes/default.php';

	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb-exceptions.inc.php';
	require THIRD_PARTY.'/adodb/adodb.inc.php';

	# Don't Cache this Page
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");

	###################################################
	### Check Input									###
	###################################################
	if ($_REQUEST['submit'])
	{
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
	if ((! $_REQUEST['submit']) or ($errorstr))
	{
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
<form method="post" action="install.php">
<table>
<tr><th colspan="2">Porchop Web Installer V0.9</th></tr>
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
	if (! $connect_success)
		install_log("Connection failed",'error');
	install_log("Connection successful");

	# Check For Existing Database
	install_log("Checking for existing schema");
	$_database->Execute("use ".$GLOBALS['_config']->database->schema);
	if ($_database->ErrorMsg())
	{
		install_log("Schema ".$GLOBALS['_config']->database->schema." not found. Creating");
		$_database->Execute("CREATE DATABASE ".$GLOBALS['_config']->database->schema.";");
		if ($_database->ErrorMsg())
		{
			install_log("Error creating database: ".$_database->ErrorMsg(),'error');
			exit;
		}
		$_database->Execute("use ".$GLOBALS['_config']->database->schema);
	}

	install_log("Building tables");
	# Metadata Tables
	$create_table_query = "
		CREATE TABLE IF NOT EXISTS `metadata_states` (
			`id` int(3) NOT NULL AUTO_INCREMENT,
			`abbrev` char(20) NOT NULL DEFAULT '',
			`name` char(50) NOT NULL DEFAULT '',
			`tax_rate` decimal(5,3) NOT NULL DEFAULT '0.000',
			`country_id` int(5) NOT NULL DEFAULT '1',
			PRIMARY KEY (`id`),
			KEY `by_abbrev` (`abbrev`),
			KEY `country_abbrev` (`country_id`,`abbrev`)
		)
	";
	$_database->Execute($create_table_query);
	if ($_database->ErrorMsg())
	{
		install_log("SQL Error creating states metadata: ".$GLOBALS['_database']->ErrorMsg(),'error');
		exit;
	}

	# See if Company Exists
	install_log("Setting up company");
	$_company = new Company();
	if ($_company->error)
	{
		install_log("Error loading company module: ".$_company->error,'error');
		exit;
	}
	list($company) = $_company->find(
		array(
			"name" => $_REQUEST['company_name'],
		)
	);

	if (! $company->id)
	{
		$company = $_company->add(
			array(
				"name" => $_REQUEST['company_name'],
			)
		);
	}
	$GLOBALS['_session']->company = $company->id;

	install_log("Setting up domain");
	$_domain = new CompanyDomain();
	list($domain) = $_domain->find(
		array(
			"name" => $domain_name,
		)
	);

	if (! $domain->id)
	{
		$domain = $_domain->add(
			array(
				"active"		=> 1,
				"status"		=> $_REQUEST["status"],
				"name"			=> $domain_name,
				"company_id"	=> $company->id,
			)
		);
		if ($_domain->error)
		{
			print "Cannot add domain: ".$_domain->error;
			exit;
		}
	}

	install_log("Creating admin account");
	$_admin = new RegisterAdmin();
	if ($_admin->error)
	{
		install_log("Error initializing Admin object: ".$_admin->error,'error');
		exit;
	}
	list($superuser) = $_admin->find();
	if ($_admin->error)
	{
		install_log("Error identifying superuser: ".$_admin->error,'error');
		exit;
	}

	if (! $superuser->id)
	{
		install_log("Setting as super user");
		$superuser = $_admin->add(
			array(
				"login"			=> $_REQUEST['admin_login'],
				"password"		=> $_REQUEST['password_1'],
				"company_id"	=> $company->id,
				"status"		=> 'active',
			)
		);
		if ($_admin->error)
		{
			install_log("Cannot add admin user: ".$_admin->error,'error');
			exit;
		}
		
		install_log("Added");
		# Must Grant Privileges to set up roles
		$_SESSION_->customer->roles = array('register manager');

		# Get Existing Roles
		install_log("Getting available roles");
		$_role = new RegisterRole();
		$roles = $_role->find();
		if ($_admin->error)
		{
			install_log("Error getting roles: ".$_admin->error,'error');
			exit;
		}

		install_log("Granting roles");
		foreach ($roles as $role)
		{		
			install_log("Granting ".$role['name']."[".$role['id']."]");
			$_admin->add_role($_admin->id,$role['id']);
			if ($_admin->error)
			{
				install_log("Error: ".$_admin->error,'error');
				exit;
			}
		}
	}

	install_log("Installation completed successfully");

	function install_log($message = '',$level = 'info')
	{
		print date('Y/m/d H:i:s');
		print " [$level]";
		print ": $message<br>\n";
		flush();
	}
?>