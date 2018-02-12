<?
	if (! $GLOBALS['_SESSION_']->customer->id) {
		header("Location: /_register/login?target=_monitor:assets");
		exit;
	}

	if (! $_REQUEST['id']) {
		$_REQUEST['id'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
	}

	if (! $_REQUEST['id']) {
		$GLOBALS['_page']->error = "Asset ID Required";
		return;
	}

	if ($_REQUEST['btn_submit']) {
		$asset = new \Monitor\Asset($_REQUEST['id']);
		$asset->update(array("name" => $_REQUEST['name']));
		if ($asset->error) {
			$GLOBALS['_page']->error = "Error setting name: ".$asset->error;
		}
		else {
			$GLOBALS['_page']->success = "Asset updated successfully";
		}
	}

	# Get Assets
	$asset = new \Monitor\Asset($_REQUEST['id']);

	# Get Sensors
	$sensorList = new \Monitor\SensorList();
	$sensors = $sensorList->find(
		array(
			"asset_id"	=> $asset->id,
			"no_hub_sensors"	=> 1,
		)
	);
	
	# Get Last Communication
	$parameters['account'] = $asset->code;
	$parameters['_limit'] = 1;
	$comm_list = new \Monitor\CommunicationList();
	list($communication) = $comm_list->find($parameters);
	if ($comm_list->error) {
		app_log("Error querying for communications: ".$comm_list->error,'error',__FILE__,__LINE__);
		$GLOBALS['_page']->error = 'Error loading comm records';
	}

	$session = new \Session\Session($communication->session_id);
	$request = $communication->request;
	$response = $communication->response;
	unset($response->header->session);
	unset($request->post->password);

	# Get Recent Messages
	$message_list = new \Monitor\MessageList();
	$messages = $message_list->find(array("asset_code" => $asset->code));

?>
