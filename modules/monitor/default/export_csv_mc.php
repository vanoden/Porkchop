<?
	$collection = new \Monitor\Collection();

	# Get Events from Database
	$collection->get($_REQUEST['code']);

	if ($collection->error) {
		print "Error: ".$collection->error;
	}
	elseif (! $collection->id) {
		print "Error: Collection not found";
	}
	else {
		# Collection Sensors
		$sensors = $collection->sensors();
		if ($collection->error) {
			print "Error getting sensors: ".$collection->error;
			return;
		}

		header("Content-type: text/csv");
		header("Content-disposition: attachment; filename=export.csv");
		print "Export for ".$collection->name."\r\n";
		print "Date,Asset,Sensor,Reading\r\n";

		$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);

		foreach ($sensors as $sensor) {
			app_log("Exporting data for collection '".$collection->id."' sensor '".$sensor->id,'debug',__FILE__,__LINE__);
			$readings = $collection->readings($sensor->id);
			if ($collection->error) {
				app_log("Error exporting data: ".$collection->error,'error',__FILE__,__LINE__);
			}
			else
			{
				foreach($readings as $reading) {
					$time = new DateTime('now',$timezone);
					$time->setTimeStamp($reading->timestamp);
			
					$localtime = $time->format("Y/m/d H:i");
	
					print $localtime.",".$sensor->asset->code.",".$sensor->code.",".$reading->value."\r\n";
				}
			}
		}
	}
	exit;
?>