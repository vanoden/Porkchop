<?php
	require_module('monitor');

	if (! $GLOBALS['_SESSION_']->customer->id)
	{
		header("location: /_register/login");
		exit;
	}

    $_collection = new MonitorCollection();

    # Get Collection from Database
    if ($_REQUEST['code'])
    {
        $collection = $_collection->get($_REQUEST['code']);
    }
    else
    {
        $_REQUEST['code'] = $GLOBALS['_REQUEST_']->query_vars_array[0];
        if ($_REQUEST['code'])
            $collection = $_collection->get($_REQUEST['code']);
        else
        {
            error_log("Invalid or no id given to graph");
            return;
        }
    }

    if ($_collection->error)
    {
        print "Error: ".$_collection->error;
        return;
    }
    if (! $collection->id)
    {
        print "Error: Collection not found\n";
        return;
    }
    $sensors = $_collection->sensors($collection->id);
    if ($_collection->error)
    {
        print "Error getting sensors: ".$_collection->error;
        return;
    }

    $data = array();
    foreach ($sensors as $sensor)
    {
        $readings = $_collection->readings(
			$collection->id,
			$sensor->id,
			array("_timestamp" => 1)
		);
        if ($_collection->error)
        {
            print "Error getting readings: ".$_collection->error;
            return;
        }
        foreach ($readings as $reading)
        {
            $data[sprintf("%0d",($reading->timestamp/60))*60][$sensor->code] = $reading->value;
        }
    }
?>
jsgraph = new Dygraph(
	document.getElementById("graphContainer"),
	[
<?
	$first_loop = 1;
	ksort($data);
    foreach (array_keys($data) as $x)
    {
		if ($first_loop) $first_loop = 0;
		else print ",\n";
		$thisdate = date("Y/m/d H:i",$x);
        print "\t\t[ new Date(\"$thisdate\")";
        foreach ($sensors as $sensor)
        {
            if ($data[$x][$sensor->code])
                print ",".$data[$x][$sensor->code];
            else
                print ",null";
        }
        print "]";
    }
?>	],
	{
		labels:
		[
			"x"<?
	foreach ($sensors as $sensor)
	{
	    print ",\n\t\t\t'".'<span class="sensorLabel">'.$sensor->asset_code." Sensor ".$sensor->code."</span>'";
	}
	print "\n";
?>
		],
		labelsDiv: graphLegend,
		labelsSeparateLines: true,
		labelsKMB: true,
		width: 650,
		height: 460,
		title: 'CxT',
		xlabel: 'Time',
		ylabel: 'Concentration',
		axisLineColor: 'black',
		connectSeparatedPoints: true,
		gridLineColor: '#111111'
	}
);
<?	exit; ?>
