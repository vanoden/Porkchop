<?
	$GLOBALS['_page']->instruction = "Set filters and click 'Submit'";
	if (role('monitor manager')) {
		if ($_REQUEST['btn_submit']) {
			$parameters = array();
			if ($_REQUEST['account_code'])
				$parameters['account'] = $_REQUEST['account_code'];
			if ($_REQUEST['_active'])
				$parameters['date_start'] = date('Y-m-d H:i:s',time() - 300);
			if ($_REQUEST['date_start'])
				$parameters['date_start'] = $_REQUEST['date_start'];
			$parameters['_active'] = $_REQUEST['_active'];
			if ($_REQUEST['max_records']) 
				$parameters['_limit'] = $_REQUEST['max_records'];

			# Get Sessions
			$commList = new \Monitor\CommunicationList();
			$communications = $commList->find($parameters);
			if ($commList->error) {
				app_log("Error querying for communications: ".$commList->error,'error',__FILE__,__LINE__);
				$GLOBALS['_page']->error = 'Error loading comm records';
			}
		}
		else {
			$communications = array();
			$parameters['_active'] = 0;
			$parameters['date_start'] = date('m/d/Y H:i',time() - 300);
			$parameters['_limit'] = 10;
		}

		# Get Accounts
		$customerlist = new \Register\CustomerList();
		$accounts = $customerlist->find();
	}
	elseif ($GLOBALS['_SESSION_']->customer->id) {
		$GLOBALS['_page']->error = "You do not have permissions for this view";
		return;
	}
	else {
		header("location: /_register/login?target=_monitor:comm_dashboard");
		exit;
	}
	
	function cleanUp($string) {
		$string = prettyPrint($string);
		//$string = preg_replace('/\r?\n/','\n',$string);
		$string = preg_replace('/\'/',"'",$string);
		return $string;
	}
?>
