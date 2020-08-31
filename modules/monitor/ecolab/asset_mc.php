<?php
	$page = new \Site\Page();
	$page->requireAuth();

	if ($_REQUEST['id']) {
		$asset = new \Monitor\Asset($_REQUEST['id']);
	}
	elseif ($_REQUEST['code']) {
		$asset = new \Monitor\Asset();
		$asset->getSimple($_REQUEST['code']);
	}
	elseif (isset($GLOBALS['_REQUEST_']->query_vars_array[0])) {
		$asset = new \Monitor\Asset();
		$asset->getSimple($GLOBALS['_REQUEST_']->query_vars_array[0]);
		if ($asset->error) {
			$page->addError("Error loading asset: ".$asset->error);
			return;
		}
	}
	else {
		$page->addError("Monitor Not Specified");
		return;
	}

	if (! $asset->id) {
		$page->addError("Monitor not found");
	}
	elseif ($asset->organization->id != $GLOBALS['_SESSION_']->customer->organization->id && ! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) {
		$page->addError("Permission Denied");
	}
	else {
		if (isset($_REQUEST['btn_submit'])) {
			$asset->update(array("name" => $_REQUEST['name']));
			if ($asset->error) {
				$page->addError("Error setting name: ".$asset->error);
			}
			else {
				$page->success = "Monitor updated successfully";
			}
		}
	
		# Get Sensors
		$sensorList = new \Monitor\SensorList();
		app_log("Getting Sensors",'trace',__FILE__,__LINE__);
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
		app_log("Getting Communications",'trace',__FILE__,__LINE__);
		list($communication) = $comm_list->find($parameters);
		if ($comm_list->error) {
			app_log("Error querying for communications: ".$comm_list->error,'error',__FILE__,__LINE__);
			$page->addError('Error loading comm records');
		}
	
		$session = new \Site\Session($communication->session->id);
		$request = $communication->request;
		$response = $communication->response;
		unset($response->header->session);
		unset($request->post->password);
	
		# Get Recent Messages
		app_log("Getting Messages",'trace',__FILE__,__LINE__);
		$message_list = new \Monitor\MessageList();
		$messages = $message_list->find(array("asset_id" => $asset->id,'_limit' => 5));
		if ($message_list->error) {
			$page->addError("Error loading messages: ".$message_list->error);
		}
	}

	app_log("Populating view",'trace',__FILE__,__LINE__);
