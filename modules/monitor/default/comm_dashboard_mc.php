<?php
	$page = new \Site\Page();
	$page->requirePrivilege('see site reports');
	$page->instruction = "Set filters and click 'Submit'";

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array();
		if ($_REQUEST['account_code'])
			$parameters['account'] = $_REQUEST['account_code'];
		if ($_REQUEST['_active'])
			$parameters['date_start'] = date('Y-m-d H:i:s',time() - 900);
		if ($_REQUEST['date_start'])
			$parameters['date_start'] = $_REQUEST['date_start'];
		$parameters['_active'] = $_REQUEST['_active'];
		if ($_REQUEST['max_records']) 
			$parameters['_limit'] = $_REQUEST['max_records'];
	}
	elseif (isset($_REQUEST['account_code'])) {
		$parameters['account'] = $_REQUEST['account_code'];
		$parameters['_active'] = 1;
		$parameters['date_start'] = date('Y-m-d H:i:s',time() - 86400);
		$parametesr['_limit'] = 16;
	}
	else {
		$communications = array();
		$parameters['_active'] = 1;
		$parameters['date_start'] = date('Y-m-d H:i:s',time() - 86400);
		$parameters['_limit'] = 16;
	}

	# Get Sessions
	$commList = new \Monitor\CommunicationList();
	$communications = $commList->find($parameters);
	if ($commList->error) {
		app_log("Error querying for communications: ".$commList->error,'error',__FILE__,__LINE__);
		$page->error = 'Error loading comm records';
	}

	# Get Accounts
	$customerlist = new \Register\CustomerList();
	$accounts = $customerlist->find();

	function cleanUp($string) {
		$string = prettyPrint($string);
		$string = preg_replace('/\'/',"'",$string);
		return $string;
	}
