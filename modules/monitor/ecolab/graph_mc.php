<?php
	$collection_code = $GLOBALS['_REQUEST_']->query_vars_array[0];
	$collection = new \Monitor\Collection();
	$collection->get($collection_code);

	app_log("Graphing for job ".$collection->metadata('name'));
	$graph = new \Graph\Line(
		array(
			'height'	=> 500,
			'width'		=> 800
		)
	);

	$graph->axisLabel('y','Concentration (PPM)');
	$graph->axisLabel('x','Time');
	$graph->setBorder('x',70);
	$graph->setBorder('y',60);

	$graph->setPadding('left',10);
	$graph->setPadding('right',20);
	$graph->setPadding('top',20);
	$graph->setPadding('bottom',10);

	$graph->setColor('axis','#000000');
	$graph->setColor('background','#ffffff');
	$graph->setColor('foreground','#ffffff');
	$graph->setColor('label','#994466');
	$graph->setColor('grid','#888888');

	# Add Data To Graph
	app_log("Getting sensors for collection ".$collection->id);
	$sensors = $collection->sensors();
	app_log("Found ".count($sensors)." sensors");
	$i = 0;
	foreach ($sensors as $sensor) {
		app_log("Getting readings for sensor ".$sensor->id);
		$readings = $collection->readings($sensor->id);
		$cnt = 0;
		app_log(count($readings)." readings");
		foreach ($readings as $reading) {
			$graph->addPoint($i,$reading->timestamp,$reading->value);
			$cnt ++;
		}
		$i ++;
	}

	# Build Graph Image
	$graph->build();
	app_log("Graph generated successfully");
	exit;
