<?php
	###################################################
	### manage_account.php							###
	### Manage account information.					###
	### A. Caravello 6/1/2022						###
	###################################################
	### This file and its contents belong to		###
	### Root Seven Technologies.					###
	###################################################
	### Modifications								###
	###################################################

	###################################################
	### Load Dependencies							###
	###################################################
	$_SERVER['SERVER_NAME'] = "localhost";

	# Load Config
	require '../config/config.php';

	# General Utilities
	require INCLUDES.'/functions.php';
	spl_autoload_register('load_class');

	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb-php/adodb.inc.php';

	# Debug Variables
	$_debug_queries = array();

	###################################################
	### User Input									###
	###################################################
	$options = array(
		"force"	=> false
	);
	$action = null;

	$roles = array();
	for ($argpos = 1; $argpos < count($argv); $argpos ++) {
		if (preg_match('/^\-\-([\w\-\.\_]+)/',$argv[$argpos],$matches)) {
			$action = $matches[1];
		}
		elseif (preg_match('/^\-(\w)$/',$argv[$argpos],$matches)) {
			if ($matches[1] == 'f') $options['force'] == true;
		}
		elseif ($action == "add-role" && isset($roleName)) {
			$roleDesc = $argv[$argpos];
		}
		elseif (empty($userCode)) {
			$userCode = $argv[$argpos];
		}
		else {
			array_push($roles,$argv[$argpos]);
		}
	}

	###################################################
	### Connect to Logger							###
	###################################################
	$logger = \Site\Logger::get_instance(array('type' => APPLICATION_LOG_TYPE,'path' => APPLICATION_LOG));
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
	### Initialize Common Objects					###
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
		$logger->write("Error connecting to database: ".$_database->ErrorMsg(),'error');
		exit;
	}
	$logger->write("Database Initiated",'trace');

    $_CACHE_ = \Cache\Client::connect($GLOBALS['_config']->cache->mechanism,$GLOBALS['_config']->cache);
    if ($_CACHE_->error()) $logger->write('Unable to initiate Cache client: '.$_CACHE_->error(),'error');
    $logger->write("Cache Initiated",'trace',__FILE__,__LINE__);

	###################################################
	### Main Procedure								###
	###################################################
	$GLOBALS['_SESSION_'] = new \Site\Session();
	$GLOBALS['_SESSION_']->elevate();

	if (isset($userCode)) {
		$user = new \Register\Customer();
		if (! $user->get($userCode)) {
			print "User $userCode not found\n";
			exit;
		}
	}
		
	if (isset($action) && $action == "get-users") {
		$roleList = new \Register\RoleList();
		$roles = $roleList->find();
		foreach ($roles as $role) {
			printf("%-30s: %s\n",$role->name,$role->description);
		}
		exit;
	}
	elseif (isset($action) && $action == "get-user") {
		$userCode = $argv[2];
		if ($user->id) {
			print $user->code." ".$user->full_name()."\n";
			$roles = $user->roles();
			print "Roles:\n";
			print_r($roles,false);
		}
		else {
			print "User $userCode not found\n";
		}
		exit;
	}
	elseif (isset($action) && $action == "add-user") {
		if (preg_match('/^\w[\w\-\.\_\s]+$/',$userCode)) {
			if ($user->id) {
				print "Account already exists\n";
				exit;
			}
			elseif ($user->add(array("login" => $userCode))) {
				print "User ".$user->id." added\n";
				exit;
			}
			else {
				print "Error: ".$user->error()."\n";
			}
		}
		exit;
	}
	elseif (isset($action) && $action == "get-roles") {
		$roles = $user->roles();
		foreach ($roles as $role) {
			printf("\t%-30s%s\n",$role->name,$role->description);
		}
		exit;
	}
	elseif ($action == "add-roles") {
		foreach ($roles as $roleName) {
			$role = new \Register\Role();
			if (! $role->get($roleName)) {
				print "Role $roleName not found\n";
			}
			if ($user->has_role($roleName)) {
				print "User already has role $roleName\n";
			}
			elseif ($user->add_role($role->id)) {
				print "Role $roleName added\n";
			}
			else {
				print "Error: ".$user->error()."\n";
				exit;
			}
		}
	}
?>
