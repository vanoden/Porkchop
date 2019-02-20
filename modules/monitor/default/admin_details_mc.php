<?php
	$page = new \Site\Page('spectros','admin_details');

	if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) return;

	if (preg_match('/^\d+$/',$_REQUEST['id'])) {
		// Get Asset
		$asset = new \Monitor\Asset($_REQUEST['id']);
		if ($asset->error) $page->error = "Error loading asset: ".$asset->error;
	}
	else {
		$asset = new \Monitor\Asset();
		if ($asset->error) $page->error = "Error initializing asset: ".$asset->error;
	}

	if (! $asset->id) {
		$product = '';
		if (! isset($_REQUEST['asset_code'])) $_REQUEST['asset_code'] = $_REQUEST['code'];
		if (! isset($_REQUEST['asset_code'])) $_REQUEST['asset_code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
		if (! isset($_REQUEST['product_code'])) $_REQUEST['product_code'] = $GLOBALS['_REQUEST_']->query_vars_array[1];
		if (isset($_REQUEST['product_code'])) {
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if ($product->error) {
				$page->error = "Error finding product: ".$product->error;
				return;
			}
		}
		elseif ($_REQUEST['product_id']) {
			$product = new \Product\Item($_REQUEST['product_id']);
		}

		if ($_REQUEST['asset_code'] && isset($product->id)) {
			$assetlist = new \Monitor\AssetList();
			list($asset_found) = $assetlist->find(
				array(
					"code"			=> $_REQUEST['asset_code'],
					"product_id"	=> $product->id
				)
			);
			if ($assetlist->error) $page->error = "Error loading asset: ".$assetlist->error;
			if (isset($asset_found->id)) $asset = $asset_found;
		}
	}

	# Handle Updates
	if ($_REQUEST['method']) {
		if ($asset->id) {
			# Update Existing Asset
			$asset->update(
				array(
					"product_id"		=> $_REQUEST['product_id'],
					"organization_id"	=> $_REQUEST['organization_id'],
				)
			);
			$page->success = "Asset ".$asset->code." updated";
		}
		else {
			# Add New Asset
			$asset->add(
				array(
					"product_id"		=> $_REQUEST['product_id'],
					"code"				=> $_REQUEST['asset_code'],
					"organization_id"	=> $_REQUEST['organization_id'],
				)
			);
			if ($asset->error) $page->error = "Error adding asset: ".$asset->error;
		}

		if ($asset->id) {
			$asset->setMetadata('software_version',$_REQUEST['software_version']);
			$asset->setMetadata('display_type',$_REQUEST['display_type']);
			$asset->setMetadata('date_shipped',get_mysql_date($_REQUEST['date_shipped']));

			while (list($id) = each($_REQUEST['sensor_code'])) {
				if (! $_REQUEST['sensor_code'][$id]) continue;
				$sensor = new \Monitor\Sensor($id);
				if ($sensor->id) {
					# Update Sensor
					$sensor->update(
						array(
							"code"		=> $_REQUEST['sensor_code'][$id],
							"units"		=> $_REQUEST['units'][$id],
							"model_id"	=> $_REQUEST['model_id'][$id]
						)
					);
					if ($sensor->error) $page->error .= "<br>Error updating sensor ".$_REQUEST['sensor_code'].": ".$sensor->error;
				}
				else {
					$sensor->add(
						array(
							"asset_id"	=> $asset->id,
							"code"		=> $_REQUEST['sensor_code'][$id],
							"units"		=> $_REQUEST['units'][$id],
							"model_id"	=> $_REQUEST['model_id'][$id]
						)
					);
					if ($sensor->error)
						$page->error .= "<br>Error adding sensor ".$_REQUEST['sensor_code'].": ".$sensor->error;
					else
						$page->success .= "<br>Added sensor ".$_REQUEST['sensor_code'][$id];
				}
			}
		}
	}
	
	if (isset($asset->id)) {
		# Get Sensors
		$sensors = $asset->sensors();
		if ($asset->error) $page->error = "Error getting sensors for asset: ".$asset->error;

		$asset_code = $asset->code;
		$product_id = $asset->product->id;

		# Get Last Communication
		$parameters['account'] = $asset->code;
		$parameters['_limit'] = 1;
		$comm_list = new \Monitor\CommunicationList();
		app_log("Getting Communications",'trace',__FILE__,__LINE__);
		list($communication) = $comm_list->find($parameters);
		if ($comm_list->error) {
			app_log("Error querying for communications: ".$comm_list->error,'error',__FILE__,__LINE__);
			$page->error = 'Error loading comm records';
		}

        $session = new \Site\Session($communication->session->id);
        $request = $communication->request;
        $response = $communication->response;
        unset($response->header->session);
        unset($request->post->password);
	}
	else {
		$asset_code = $_REQUEST['asset_code'];
		$product_id = $_REQUEST['product_id'];
	}
	
	// Reference Information
	$productlist = new \Product\ItemList();
	$products = $productlist->find(
		array(
			"type"	=> "unique"
		)
	);
		
	if ($productlist->error) $page->error = "Error getting products for selection: ".$productlist->error;
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();

	# Get Associated Tickets
	$ticketlist = new \Support\Request\ItemList();
	$tickets = $ticketlist->find(array('product_id' => $asset->product->id,'serial_number' => $asset->code,'_limit' => 1));

	# Get Sensor Models
	$modellist = new \Monitor\Sensor\ModelList();
	$models = $modellist->find();

	# Get Messages for Device
	$messageList = new \Monitor\MessageList();
	$messages = $messageList->find(array('asset_id' => $asset->id,'_limit' => 5));
