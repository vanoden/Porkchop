<?php
	$interval_seconds = 5;
	if (! $GLOBALS['_SESSION_']->customer->id) {
		print "Customer not logged in";
		return null;
	}
	if (! isset($_REQUEST['sensor_id'])) error("Invalid or no sensor id given to graph");

	if (isset($_REQUEST['organization'])) {
		if ($GLOBALS['_SESSION_']->customer->has_role('register manager')) {
			$organization = new \Register\Organization();
			$organization->get($_REQUEST['organization']);
		}
		else {
			error("No permissions to see other organizations data");
		}
	}
	else {
		$organization = $GLOBALS['_SESSION_']->customer->organization;
	}

	$sensor = new \Monitor\Sensor($_REQUEST['sensor_id']);
	if ($sensor->error) error("Error getting sensor: ".$sensor->error);
	
	$data = array();

	if (isset($_REQUEST['span_seconds'])) {
		$date_start = time() - $_REQUEST['span_seconds'];
	}
	$readings = $sensor->readings($date_start,$date_end);
	if ($sensor->error()) {
		print "Error getting readings: ".$sensor->error();
		return;
	}
	foreach ($readings as $reading) {
		$data[sprintf("%0d",($reading->timestamp/$interval_seconds))*$interval_seconds][$sensor->code] = $reading->value;
	}

	$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);

	header("Content-type: text/csv");
	$content = '';
	$labels = 'Date';
	$first_loop = 1;
	foreach (array_keys($data) as $x) {
		$time = new DateTime('@'.$x,new \DateTimeZone('UTC'));
		$time->setTimezone($timezone);

		$content .= $time->format("Y/m/d H:i:s").",";
		$last = 1;
		$i = 0;

			if ($first_loop) $labels .= ",".$sensor->code;

			if (array_key_exists($sensor->code,$data[$x])) $content .= $data[$x][$sensor->code];
			else $content .= 'null';
			$i ++;
			if ($i < $last) $content .= ",";

		$content .= "\n";
		$first_loop = 0;
	}
	print $labels."\n".$content;
	exit;
