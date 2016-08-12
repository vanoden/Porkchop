<?PHP	
	###################################################
	### cron.php									###
	### This module is a content management and 	###
	### and display system.							###
	### A. Caravello 2/3/2012						###
	###################################################
	### This file and its contents belong to		###
	### Root Seven Technologies.					###
	###################################################
	### Modifications								###
	###################################################

	error_log("###### Page: ".$_SERVER["REQUEST_URI"]."######");
	error_log("\$_REQUEST: ".print_r($_REQUEST,true));

	# Load Config
	require '/www/html/config.php';

	# Some HTTP Stuff
	$_SERVER['HTTP_HOST'] = "ops-test-01.dev.buydomains.com";
	$_SERVER['REQUEST_URI'] = $argv[1];

	# Debug Variables
	$_debug_queries = array();
	###################################################
	### Load API Objects							###
	###################################################
	# General Utilities
	require INCLUDES.'/functions.php';
	# Company Classes
	require MODULES.'/company/_classes/company.php';
	# Session Classes
	require MODULES.'/session/_classes/session.php';
	# Database Abstraction
	require THIRD_PARTY.'/adodb/adodb.inc.php';
	# Page Handling
	require MODULES.'/content/_classes/page.php';
	# Messages
	require MODULES.'/content/_classes/content.php';
	# Person (Visitor/Customer/Admin)
	require MODULES.'/register/_classes/register.php';

	###################################################
	### Initialize Common Objects					###
	###################################################
	# Connect to Database
	$_database = NewADOConnection('mysqli');
	$_database->port = $GLOBALS['_config']->database->master->port;
	$_database->Connect(
		$GLOBALS['_config']->database->master->hostname,
		$GLOBALS['_config']->database->master->username,
		$GLOBALS['_config']->database->master->password,
		$GLOBALS['_config']->database->schema
	);
	if ($_database->ErrorMsg())
	{
		print "Error connecting to database: ".$_database->ErrorMsg();
		error_log($_database->ErrorMsg());
		exit;
	}

	# Connect to Memcache if so configured
	if ($GLOBALS['_config']->cache_mechanism == 'memcache')
	{
		$_memcache = new Memcache;
		$_memcache->addServer($GLOBALS['_config']->memcache->host,$GLOBALS['_config']->memcache->port);
		$memcache_stats = @$_memcache->getExtendedStats();
		$memcache_available = (bool) $memcache_stats[$GLOBALS['_config']->memcache->host.":".$GLOBALS['_config']->memcache->port];
		if (! $memcache_available)
		{
			error_log("Memcached not reachable at ".$GLOBALS['_config']->memcache->host.":".$GLOBALS['_config']->memcache->port);
			$GLOBALS['_config']->cache_mechanism = '';
		}
		elseif (@$_memcache->connect($GLOBALS['_config']->memcache->host,$GLOBALS['_config']->memcache->port))
		{
			// Memcache Connected
		}
		else
		{
			error_log("Cannot connect to memcached");
			$GLOBALS['_config']->cache_mechanism = '';
		}
	}

	# Don't Cache this Page
	header("Expires: 0");
	header("Cache-Control: no-cache, must-revalidate");

	# Create Session
	$_session = new Session($_COOKIE['session_code']);

	if ($_session->error)
	{
		error_log($_session->error);
		die("Session Error: ".$_session->error);
	}
	if ($_session->message)
	{
	    $page_message = $_session->message;
	}

	# Get Info about person
	$_customer = new Customer($_session->customer);

	# Load Page Information
	$page_parameters = array(
		    "auth_required" => 0,
		    "style"			=> ''
	    );
	$_page = new Page($page_parameters);
	if ($_page->error)
	{
		print "Error: ".$_page->error;
		error_log($_page->error);
		exit;   
	}

	print $_page->load_template();
?>
