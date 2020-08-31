<?php
	$page = new \Site\Page();
	if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) return;
	if (isset($_REQUEST['id']) && preg_match('/^\d+$/',$_REQUEST['id'])) {
		// Get Asset
		$asset = new \Monitor\Asset($_REQUEST['id']);
		if ($asset->error) $page->addError("Error loading asset: ".$asset->error);
	} else {
		$asset = new \Monitor\Asset();
		if ($asset->error) $page->addError("Error initializing asset: ".$asset->error);
	}
	if (! $asset->id) {
		$product = '';
		if (! isset($_REQUEST['asset_code']) && isset($_REQUEST['code'])) $_REQUEST['asset_code'] = $_REQUEST['code'];
		if (! isset($_REQUEST['asset_code'])) $_REQUEST['asset_code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
		if (! isset($_REQUEST['product_code'])) $_REQUEST['product_code'] = $GLOBALS['_REQUEST_']->query_vars_array[1];
		if (isset($_REQUEST['product_code'])) {
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if (isset($product->error)) {
				$page->addError("Error finding product: ".$product->error);
				return;
			}
		} elseif ($_REQUEST['product_id']) {
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
			if ($assetlist->error) $page->addError("Error loading asset: ".$assetlist->error);
			if (isset($asset_found->id)) $asset = $asset_found;
		}
	}
	
	// check for any updates / deletes to celllar metadata
	foreach ($asset->cellularMetaData as $cellularMetaData) {
	    if (isset($_REQUEST['btn_update_cellular_' . $cellularMetaData])) {
	        var_dump('update');
    	    $asset->setMetadata($cellularMetaData, $_REQUEST['cellularMetaDataEditValue_'.$cellularMetaData]);
	    }
	    if (isset($_REQUEST['btn_delete_cellular_' . $cellularMetaData])) {
	        $asset->deleteMetadata($cellularMetaData);
	    }
	}
    
    // add metadata if requsted
	if (isset($_REQUEST['btn_add_cellular']) && $_REQUEST['btn_add_cellular'] == 'Add') {
	    $asset->setMetadata($_REQUEST['cellularMetaDataAddType'],$_REQUEST['cellularMetaDataAddValue']);
	}
	$allAssetMetaData = $asset->getMetadata();
	
	
	// Handle Updates
	if ($_REQUEST['method']) {
		if ($asset->id) {
		
			// Update Existing Asset
			$asset->update(
				array(
					"product_id"		=> $_REQUEST['product_id'],
					"organization_id"	=> $_REQUEST['organization_id'],
				)
			);
			$page->success = "Asset ".$asset->code." updated";

		} else {
		
			// Add New Asset
			$asset->add(
				array(
					"product_id"		=> $_REQUEST['product_id'],
					"code"				=> $_REQUEST['asset_code'],
					"organization_id"	=> $_REQUEST['organization_id'],
				)
			);
			if ($asset->error) $page->addError("Error adding asset: ".$asset->error);
		}

		if ($asset->id) {
			$asset->setMetadata('software_version',$_REQUEST['software_version']);
			$asset->setMetadata('display_type',$_REQUEST['display_type']);
			$asset->setMetadata('date_shipped',get_mysql_date($_REQUEST['date_shipped']));

			while (list($id) = each($_REQUEST['sensor_code'])) {
				if (! $_REQUEST['sensor_code'][$id]) continue;
				$sensor = new \Monitor\Sensor($id);

				if ($_REQUEST['system'][$id] == 1) $system = true;
				else $system = false;
				if ($sensor->id) {
				
					// Update Sensor
					$sensor->update(
						array(
							"code"		=> $_REQUEST['sensor_code'][$id],
							"units"		=> $_REQUEST['units'][$id],
							"model_id"	=> $_REQUEST['model_id'][$id],
							"system"	=> $system
						)
					);
					if ($sensor->error) $page->addError("Error updating sensor ".$_REQUEST['sensor_code'].": ".$sensor->error);
				}
				else {
					$sensor->add(
						array(
							"asset_id"	=> $asset->id,
							"code"		=> $_REQUEST['sensor_code'][$id],
							"units"		=> $_REQUEST['units'][$id],
							"model_id"	=> $_REQUEST['model_id'][$id],
							"system"	=> $system
						)
					);
					if ($sensor->error)
						$page->addError("Error adding sensor ".$_REQUEST['sensor_code'].": ".$sensor->error);
					else
						$page->success .= "<br>Added sensor ".$_REQUEST['sensor_code'][$id];
				}
			}
		}
	}
	
	if (isset($asset->id)) {
	
		// Get Sensors
		$sensors = $asset->sensors();
		if ($asset->error) $page->addError("Error getting sensors for asset: ".$asset->error);

		$asset_code = $asset->code;
		$product_id = $asset->product->id;

		// Get Last Communication
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
        if ($response->header->session) unset($response->header->session);
        if ($request->post->password) unset($request->post->password);
	} else {
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
	
	if ($productlist->error) $page->addError("Error getting products for selection: ".$productlist->error);
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();

	// Get Associated Tickets
	$ticketlist = new \Support\Request\ItemList();
	$tickets = $ticketlist->find(array('product_id' => $asset->product->id,'serial_number' => $asset->code,'_limit' => 1));

	// Get Sensor Models
	$modellist = new \Monitor\Sensor\ModelList();
	$models = $modellist->find();

	// Get Messages for Device
	$messageList = new \Monitor\MessageList();
	$messages = $messageList->find(array('asset_id' => $asset->id,'_limit' => 5));
	
	// get the 5 most recent jobs for this monitor	
	$monitorJobs = $asset->getCollections(array('_limit' => 5));
	if ($asset->error()) $page->addError($asset->error());
	
	// check if all cellular fields already populated
	$allCellularPopulated = false;
	$setCellularKeys = array_keys($allAssetMetaData);
	if (in_array('CELL_IMEI', $setCellularKeys) && in_array('SIM_ICCID', $setCellularKeys) && in_array('SIM_PHONE_NUMBER', $setCellularKeys) && in_array('SIM_VENDOR', $setCellularKeys)) $allCellularPopulated = true;

