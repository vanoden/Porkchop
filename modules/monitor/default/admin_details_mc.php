<?
	if (! $GLOBALS['_SESSION_']->customer->has_role('monitor admin')) return;

	if (preg_match('/^\d+$/',$_REQUEST['id'])) {
		# Get Asset
		$asset = new \Monitor\Asset($_REQUEST['id']);
		if ($asset->error) {
			$GLOBALS['_page']->error = "Error loading asset: ".$_asset->error;
		}
	}
	if (! $asset->id) {
		if (! $_REQUEST['asset_code']) {
			$_REQUEST['asset_code'] = $_REQUEST['code'];
		}
		if (! $_REQUEST['asset_code']) {
			$_REQUEST['asset_code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
		}
		if (! $_REQUEST['product_code']) {
			$_REQUEST['product_code'] = $GLOBALS['_REQUEST_']->query_vars_array[1];
		}
		if ($_REQUEST['product_code']) {
			$product = new \Product\Item();
			$product->get($_REQUEST['product_code']);
			if ($_product->error) {
				$this->error = "Error finding product: ".$_product->error;
				return;
			}
		}
		elseif ($_REQUEST['product_id']) {
			$product = new \Product\Item($_REQUEST['product_id']);
		}
		if ($_REQUEST['asset_code'] && isset($product->id)) {
			$asset = new \Monitor\Asset();
			$asset->get($_REQUEST['asset_code'],$product->id);
			if ($asset->error) $GLOBALS['_page']->error = "Error loading asset: ".$_asset->error;
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
			$GLOBALS['_page']->success = "Asset ".$asset->code." updated";
		}
		else {
			# Add New Asset
			$asset = new \Monitor\Asset();
			$asset->add(
				array(
					"product_id"		=> $_REQUEST['product_id'],
					"code"				=> $_REQUEST['asset_code'],
					"organization_id"	=> $_REQUEST['organization_id'],
				)
			);
			if ($asset->error) $GLOBALS['_page']->error = "Error adding asset: ".$asset->error;
		}

		if ($asset->id) {
			while (list($id) = each($_REQUEST['sensor_code'])) {
				if (! $_REQUEST['sensor_code'][$id]) continue;
				if ($id) {
					$sensor = new \Monitor\Sensor($id);
					# Update Sensor
					$sensor->update(
						array(
							"code"		=> $_REQUEST['sensor_code'][$id],
							"units"		=> $_REQUEST['units'][$id]
						)
					);
					if ($sensor->error)
						$GLOBALS['_page']->error .= "<br>Error updating sensor ".$_REQUEST['sensor_code'][$id].": ".$sensor->error;
				}
				else {
					$sensor = new \Monitor\Sensor();
					$sensor->add(
						array(
							"asset_id"	=> $asset->id,
							"code"		=> $_REQUEST['sensor_code'][$id],
							"units"		=> $_REQUEST['units'][$id]
						)
					);
					if ($sensor->error)
						$GLOBALS['_page']->error .= "<br>Error adding sensor ".$_REQUEST['sensor_code'][$id].": ".$sensor->error;
					else
						$GLOBALS['_page']->success .= "<br>Added sensor ".$_REQUEST['sensor_code'][$id];
				}
			}
		}
	}

	if (isset($asset->id)) {
		# Get Sensors
		$sensors = $asset->sensors($asset->id);
		if ($asset->error) {
			$GLOBALS['_page']->error = "Error getting sensors for asset: ".$asset->error;
		}

		# Get Calibration History
		$verificationlist = new \Spectros\CalibrationVerificationList();
		$verifications = $verificationlist->find(array("asset_id" => $asset->id));
		if ($verificationlist->error) {
			$GLOBALS['_page']->error = "Error getting calibration history: ".$verificationlist->error;
			return null;
		}

		$asset_code = $asset->code;
		$product_id = $asset->product_id;
	}
	else {
		$asset_code = $_REQUEST['asset_code'];
		$product_id = $_REQUEST['product_id'];
	}
	
	# Reference Information
	$productlist = new \Product\ItemList();
	$products = $productlist->find(
		array(
			"type"	=> "unique"
		)
	);
	if ($productlist->error) {
		$GLOBALS['_page']->error = "Error getting products for selection: ".$productlist->error;
	}
	$organizationlist = new \Register\OrganizationList();
	$organizations = $organizationlist->find();

	# Get Associated Tasks
	if ($asset->id) {
		$catalog = new \Action\TaskList();
		$tasks = $catalog->find(array("asset_id" => $asset->id));
		$catalog = new \Action\Event();
		$events = $catalog->search("ActionTask",array("asset_code" => $asset->code));
	}
?>
