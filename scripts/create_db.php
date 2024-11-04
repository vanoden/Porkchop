<?php
	###################################################################
	### scripts/create_db.php										###
	###																###
	### Initialize the Database										###
	###																###
	### Copyright (C) 2014 Anthony Caravello						###
	###																###
	### This program is free software: you can redistribute it and/	###
	### or modify it under the terms of the GNU General Public		###
	### License as published by the Free Software Foundation, 		###
	### either version 3 of the License, or any later version.		###
	###																###
	### This program is distributed in the hope that it will be		###
	### useful, but WITHOUT ANY WARRANTY; without even the implied 	###
	### warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR		###
	### PURPOSE.  See the GNU General Public License for more		###
	### details.													###
	###																###
	### You should have received a copy of the GNU General Public	###
	### License along with this program.  If not, see				###
	### <http://www.gnu.org/licenses/>.								###
	###################################################################

	#error_reporting(E_ERROR);
	ini_set('display_errors',1);
	###################################################
	### Command Line Arguments						###
	###################################################
	$cwd = getcwd();
	$path = $argv[0];

	if (isset($argv[1])) $config_path = $argv[1];
	else $config_path = $cwd;

	if (! file_exists($config_path."/config.php")) {
		if (file_exists($config_path."/config.php.dist")) {
			print "config directory $config_path found, but only distribution config found\n";
			exit;
		}
		elseif (file_exists($config_path."/config/config.php")) 
			$config_path = $config_path."/config";
		elseif (file_exists($config_path."/config/config.php.dist")) {
			print "config directory $config_path/config found, but only distribution config found\n";
			exit;
		}
		else {
			print "Configuration path not found\n";
			exit;
		}
	}

	###################################################
	### Load Dependencies							###
	###################################################
	# Load Config
	require $config_path."/config.php";
	
	# General Utilities
	require INCLUDES.'/functions.php';
	spl_autoload_register('load_class');

	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb-php/adodb.inc.php';

	# Debug Variables
	$_debug_queries = array();

    ###################################################
    ### Connect to Logger                           ###
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
	### Prompt for Info								###
	###################################################
	$login = readline("Database Administrator Username: ");
	$password = getPassword("Database Administrator Password: ");

	###################################################
	### A Little Validation							###
	###################################################
	if (! preg_match('/^\w[\w\-\_]*$/',$GLOBALS['_config']->database->schema)) {
		print "Invalid schema name\n";
		exit;
	}
	if (! preg_match('/^\w[\w\-\_]*$/',$GLOBALS['_config']->database->master->username)) {
		print "Invalid username\n";
		exit;
	}
	if (! preg_match('/^\w[\w\-\_]*$/',$GLOBALS['_config']->database->master->password)) {
		print "Invalid password\n";
		exit;
	}
	###################################################
	### Connect to Database							###
	###################################################
	# Connect to Database
	$_database = NewADOConnection('mysqli');
	if ($GLOBALS['_config']->database->master->port) $_database->port = $GLOBALS['_config']->database->master->port;
	$_database->Connect(
		$GLOBALS['_config']->database->master->hostname,
		$login,
		$password
	);
	if ($_database->ErrorMsg()) {
		print "Error connecting to database:<br>\n";
		print $_database->ErrorMsg();
		$logger->write("Error connecting to database: ".$_database->ErrorMsg(),'error');
		exit;
	}
	print "Database connected\n";

	# Check for Schema
	$found_schema = false;
	$check_schema_query = "
		SHOW databases
	";
	$rs = $_database->Execute($check_schema_query);
	while (list($schema) = $rs->FetchRow()) {
		if ($schema == $GLOBALS['_config']->database->schema) {
			$found_schema = true;
			continue;
		}
	}

	if ($found_schema) {
		print $GLOBALS['_config']->database->schema." already present\n";
	}
	else {
		print "Creating Database Schema ".$GLOBALS['_config']->database->schema."\n";
		$add_schema_query = "
			CREATE DATABASE `".$GLOBALS['_config']->database->schema."`";
		$GLOBALS['_database']->Execute($add_schema_query);
		if ($GLOBALS['_database']->ErrorMsg()) {
			print "Error: ".$GLOBALS['_database']->ErrorMsg();
			exit;
		}
	}

	# Get Host IP
	$get_connection_query = "
		SELECT host FROM information_schema.processlist
		WHERE	user = ?
		AND		info LIKE 'SELECT host from information_schema%'";
	$rs = $GLOBALS['_database']->Execute($get_connection_query,array($login));
	if (! $rs) {
		print "SQL Error getting connection: ".$GLOBALS['_database']->ErrorMsg();
		exit;
	}
	list($host) = $rs->FetchRow();
	if (! isset($host)) {
		print "Could not get host connection info\n";
		exit;
	}

	if (preg_match('/^(localhost|[\d\.]+)\:\d+$/',$host,$matches)) {
		$host = $matches[1];
	}
	else {
		print "Unparseable host name\n";
		exit;
	}

	# Grant Privileges
	$add_account_query = "
		GRANT all privileges on ".$GLOBALS['_config']->database->schema.".* TO '".$GLOBALS['_config']->database->master->username."'@'".$host."' identified by '".$GLOBALS['_config']->database->master->password."'";

	$GLOBALS['_database']->Execute($add_account_query);
	if ($GLOBALS['_database']->ErrorMsg()) {
		print "Error adding account: ".$GLOBALS['_database']->ErrorMsg()."\n";
		exit;
	}
	print "Account created\n";

	$set_schema_query = "use ".$GLOBALS['_config']->database->schema;
	$GLOBALS['_database']->Execute($set_schema_query);
	if ($GLOBALS['_database']->ErrorMsg()) {
		print "Error changing schema: ".$GLOBALS['_database']->ErrorMsg();
		exit;
	}

	print "Update Company Schema\n";
	$schema = new \Company\Schema();
	if (! $schema->upgrade()) {
		print "Company schema upgrade failed: ".$schema->error()."\n";
	}

	print "Update Site Schema\n";
	$schema = new \Site\Schema();
	if (! $schema->upgrade()) {
		print "Site schema upgrade failed: ".$schema->error()."\n";
	}

	print "Update Geography Schema\n";
	$schema = new \Geography\Schema();
	if (! $schema->upgrade()) {
		print "Geography schema upgrade failed: ".$schema->error()."\n";
	}

	print "Update Register Schema\n";
	$schema = new \Register\Schema();
	if (! $schema->upgrade()) {
		print "Register schema upgrade failed: ".$schema->error()."\n";
	}

	print "Update Contact Schema\n";
	$schema = new \Contact\Schema();
	if (! $schema->upgrade()) {
		print "Contact schema upgrade failed: ".$schema->error()."\n";
	}

	print "Update Content Schema\n";
	$schema = new \Content\Schema();
	if (! $schema->upgrade()) {
		print "Content schema upgrade failed: ".$schema->error()."\n";
	}

	print "Update Navigation Schema\n";
	$schema = new \Site\Navigation\Schema();
	if (! $schema->upgrade()) {
		print "Navigation schema upgrade failed: ".$schema->error()."\n";
	}

	###################################################
	### Functions									###
	###################################################
	function usage() {
		print "Error: login and password required as command line arguments\n";
		print "php create_db.php login password\n";
		exit;
	}

	function getPassword($prompt) {
		print $prompt;
		system('stty -echo');
		$password = trim(fgets(STDIN));
		system('stty echo');
		print "\n";
		return $password;
	}
