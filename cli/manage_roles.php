<?php
	###################################################
	### manage_roles.php							###
	### Add/Remove Specified privileges for a 		###
	### specified role.								###
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

	$privileges = array();
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
		elseif (empty($roleName)) {
			$roleName = $argv[$argpos];
		}
		else {
			array_push($privileges,$argv[$argpos]);
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

	if (isset($action) && $action == "get-roles") {
		$roleList = new \Register\RoleList();
		$roles = $roleList->find();
		foreach ($roles as $role) {
			printf("%-30s: %s\n",$role->name,$role->description);
		}
		exit;
	}
	elseif (isset($action) && $action == "get-role") {
		$roleName = $argv[2];
		$role = new \Register\Role();
		if ($role->get($roleName)) {
			printf ("%-30s: %s\n",$role->name,$role->description);
			$privileges = $role->privileges();
			print "Privileges:\n";
			foreach ($privileges as $privilege) {
				$description = $privilege->description;
				if (empty($description)) $description = "No description";
				printf ("\t%-17s::%-28s: %s\n",$privilege->module,$privilege->name,$description);
			}
		}
		else {
			print "Role $roleName not found\n";
		}
		exit;
	}
	elseif (isset($action) && $action == "add-role") {
		if (preg_match('/^\w[\w\-\.\_\s]+$/',$roleName)) {
			$role = new \Register\Role();
			if ($role->get($roleName)) {
				print "Role already exists\n";
				exit;
			}
			elseif ($role->add(array("name" => $roleName,"description" => $roleDesc))) {
				print "Role ".$role->id." added\n";
				exit;
			}
			else {
				print "Error: ".$role->error()."\n";
			}
		}
	}

	$role = new \Register\Role();
	if (! $role->get($roleName)) {
		if ($role->error()) {
			print "Cannot find role $roleName: ".$role->error()."\n";
		}
		else {
			print "Role $roleName not found\n";
		}
		exit;
	}

	foreach ($privileges as $privilegeName) {
		if ($role->has_privilege($privilegeName)) {
			print "$roleName already has $privilegeName privilege\n";
		}
		elseif ($role->error()) {
			print "Error checking privilege: ".$role->error()."\n";
			exit;
		}
		else {
			$privilege = new \Register\Privilege();
			if ($privilege->get($privilegeName)) {
				print "Adding privilege $privilegeName\n";
				if ($role->addPrivilege($privilege->id)) {
					print "$privilegeName added\n";
				}
				else {
					print "Cannot add privilege: ".$role->error()."\n";
				}
			}
			else {
				print "Privilege ".$privilegeName." not found\n";
			}
		}
	}
?>
