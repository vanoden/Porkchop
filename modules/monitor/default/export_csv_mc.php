<?
	require_module("monitor");
	$_collection = new MonitorCollection();

	# Get Events from Database
	$collection = $_collection->details($_REQUEST['collection_id']);

	if ($_collection->error)
	{
		print "Error: ".$_collection->error;
	}

	# Collection Sensors
    $sensors = $_collection->sensors($collection->id);
    if ($_collection->error)
    {
        print "Error getting sensors: ".$_collection->error;
        return;
    }

	header("Content-type: text/csv");
	header("Content-disposition: attachment; filename=export.csv");
	print "date,asset,sensor,value\r\n";

	$timezone = new DateTimeZone($GLOBALS['_SESSION_']->timezone);

	foreach ($sensors as $sensor)
	{
		app_log("Exporting data for collection '".$collection->id."' sensor '".$sensor->id,'debug',__FILE__,__LINE__);
		$readings = $_collection->readings($collection->id,$sensor->id);
		if ($_collection->error)
		{
			app_log("Error exporting data: ".$_collection->error,'error',__FILE__,__LINE__);
		}
		else
		{
			foreach($readings as $reading)
			{
				$time = new DateTime('now',$timezone);
				$time->setTimeStamp($reading->timestamp);
		
				$localtime = $time->format("Y/m/d H:i");

				print $localtime.",".$sensor->asset_code.",".$sensor->code.",".$reading->value."\r\n";
			}
		}
	}
	exit;
?>