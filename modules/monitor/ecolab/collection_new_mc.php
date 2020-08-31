<?php
	$page = new \Site\Page();
	$page->requireAuth();

	if (isset($_REQUEST['btn_submit'])) {
		$parameters = array(
			'organization_id'	=> $GLOBALS['_SESSION_']->customer->organization->id
		);

		$asset = new \Monitor\Asset($_REQUEST['asset_id']);
		if (! $asset->id) {
			$page->addError("Asset not found: ".$asset->error());
			return;
		}

		$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);
		if (isset($_REQUEST['date_start']) && get_mysql_date($_REQUEST['date_start'])) {
			$time = new DateTime(get_mysql_date($_REQUEST['date_start']),$timezone);
			$parameters['timestamp_start'] = $time->getTimeStamp();
		}
		elseif (isset($_REQUEST['timestamp_start'])) {
			$parameters['timestamp_start'] = $_REQUEST['timestamp_start'];
		}
		else $parameters['timestamp_start'] = 'now';

		if (isset($_REQUEST['date_end']) && get_mysql_date($_REQUEST['date_end'])) {
			$time = new DateTime(get_mysql_date($_REQUEST['date_end']),$timezone);
			$parameters['timestamp_end'] = $time->getTimeStamp();
		}
		elseif ($_REQUEST['timestamp_end']) {
			$parameters['timestamp_end'] = $_REQUEST['timestamp_end'];
		}
		else $parameters['timestamp_end'] = '+2 days';

		$parameters['dashboard_id'] = $_REQUEST['dashboard_id'];
		$parameters['type'] = $_REQUEST['type'];

		$sensors = $asset->sensors();

		$collection = new \Monitor\Collection();
		if ($collection->add($parameters)) {
			$collection->setMetadata('name',$_REQUEST['name']);
			if (strlen($_REQUEST['location'])) $collection->setMetadata('location',$_REQUEST['location']);
			if (strlen($_REQUEST['customer'])) $collection->setMetadata('customer',$_REQUEST['customer']);
			if (strlen($_REQUEST['commodity'])) $collection->setMetadata('commodity',$_REQUEST['commodity']);
			if (is_numeric($_REQUEST['hours'])) $collection->setMetadata('time_span',$_REQUEST['hours'] * 3600);
			if ($collection->dashboard($parameters['dashboard_id'])) {
				foreach ($sensors as $sensor) {
					if ($sensor->system()) continue;
					$collection->addSensor($sensor->id);
				}
				header("location: /_monitor/dashboard/".$collection->code);
				return;
			}
			else $page->addError($collection->error());
		}
		else $page->addError($collection->error());
	}

	$dashboardList = new \Monitor\DashboardList();
	$dashboards = $dashboardList->find(array('status' => 'PUBLISHED'));

	$assetList = new \Monitor\AssetList();
	$assets = $assetList->find();
