<?php
	# Root Seven Graph Functions
	include ("/home/php_inc/graphs.php");

	# Get Event Info
	$event_info = get_event($id);

	# Graph Info
	$filename = "$session_code.png";
	$filepath = "/home/web/".$domain."/graphs";
	$graph["title"] = $event_info["name"];
	$graph["border"]["top"] = 15;
	$graph["border"]["bottom"] = 15;
	$graph["border"]["left"] = 5;
	$graph["border"]["right"] = 15;
	$graph["label"] = "Fumigation Graph/nTest Event";
	$graph["file"] = "$filepath/$filename";
	$graph["height"] = 400;
	$graph["width"] = 650;
	$graph["block"] = 0;
	$graph["threshold"]["vertical"]["min"] = $event_info["alarm_min"];
	$graph["color"]["background"] = "EEEEEE";
	$graph["color"]["view"] = "FEFEFE";
	$graph["color"]["axis"] = "000000";

#	Dennis new colors below
	$graph["color"]["data"][0] = "990000";
	$graph["color"]["data"][1] = "FF0000";
	$graph["color"]["data"][2] = "000066";
	$graph["color"]["data"][3] = "008000";
	$graph["color"]["data"][4] = "CC9900";
	$graph["color"]["data"][5] = "CC00CC";
	$graph["color"]["threshold"]["vertical"]["min"] = "ff0000";
	$graph["axis"]["vertical"]["color"] = "000000";
	$graph["axis"]["vertical"]["label"] = "Concentration";
	$graph["axis"]["vertical"]["units"] = "OZ";
	$graph["axis"]["vertical"]["padding"] = 25;
	$graph["axis"]["vertical"]["min"] = 0;
	$graph["axis"]["vertical"]["max"] = 0; # Auto Calculate
	$graph["axis"]["vertical"]["grid"] = 5;
	$graph["axis"]["horizontal"]["color"] = "000000";
	$graph["axis"]["horizontal"]["label"] = "Time";
	$graph["axis"]["horizontal"]["units"] = "Hours";
	$graph["axis"]["horizontal"]["padding"] = 25;
	$graph["axis"]["horizontal"]["min"] = 0;
	$graph["axis"]["horizontal"]["max"] = 0; # Auto Calculate
	$graph["axis"]["horizontal"]["grid"] = 5;
	$graph["description"][0] = "Monitor: Test Monitor";
	$graph["description"][1] = "Date: ".$event_info["date_start"];
	$graph["description"][2] = "Time: ".$event_info["time_start"];

	# Get Event Hubs
	$hubs = get_hubs($id);
	$mcnt = 0;

	# Loop Through Hubs
	while ($hub_info = mysql_fetch_array($hubs))
	{
		# Get Monitor Events
		$monitors = get_event_monitors($id, $hub_info["hub_id"]);

		# Loop Through Monitors
		while ($monitor_info = mysql_fetch_array($monitors))
		{
			# Get Event Zones
			$zones = get_event_points($id,$monitor_info["monitor_id"]);

			# Loop through Zones
			while ($zone_info = mysql_fetch_array($zones))
			{
				# Skip Unselected Points if One Selected
				if (($id2) and ($id2 != $zone_info["point_id"])) continue;

				# Get Data For Monitor
				$points = get_data_points($id,$zone_info["point_id"]);

				# Label For Data
				if ($zone_info["location"])
					$graph["data"][$mcnt]["label"] = $zone_info["location"];
				else
					$graph["data"][$mcnt]["label"] = $monitor_info["label"]." ".$zone_info["point_id"];

				# Loop Through Points
				$pcnt = 0;
				#print "<!-- Started: ".$event_info["seconds_start"]." -->\n";
				while ($point_info = mysql_fetch_array($points))
				{
					# Look For Anomolies
					$avg = ($prev_2_y + $prev_1_y) / 2;
					if ($point_info["value"] < ($avg * .84))
					{
						#print $point_info["value"]." < ".($cmp * )."<br>\n";
						continue;
					}
					
					# Store Prevs to Identify Severe Anomolies
					$prev_2_y = $prev_1_y;
					$prev_1_y = $point_info["value"];
					
					#print "<!-- Value: ".(($point_info["date_point"] - $event_info["seconds_start"])/60)." -> ".$point_info["value"]."-->\n";
					$x_value = ($point_info["date_point"] - $event_info["seconds_start"])/3600;
					if ($x_value < 0) continue;

					$y_value = $point_info["value"];
					$graph["data"][$mcnt][$pcnt] = Array($x_value,$y_value);
					if ($x_value > $max_x_value) $max_x_value = $x_value;
					if ($y_value > $max_y_value) $max_y_value = $y_value;
					
					
					# Store Last Value for Zone
					$last_x[$mcnt] = $x_value;
					$last_y[$mcnt] = $y_value;
					
					# Increment Count of Points
					$pcnt ++;
				}
				# Increment Counter
				$mcnt ++;
			}
		}
	}
	if ($graph["axis"]["horizontal"]["max"] < $max_x_value)
		$graph["axis"]["horizontal"]["max"] = (($max_x_value - $graph["axis"]["horizontal"]["min"]) * 1.02) + $graph["axis"]["horizontal"]["min"];
	if ($graph["axis"]["vertical"]["max"] < $max_y_value)
		$graph["axis"]["vertical"]["max"] = (($max_y_value - $graph["axis"]["vertical"]["min"])* 1.02) + $graph["axis"]["vertical"]["min"];

	# Generate Graph for dev site
	$result = line_graph($graph);
	{
		if ($result != 1)
		{
			$error_msg = $result;
		}
	}


	# Generate Graph for live site
	$filename = "$session_code.png";
	$filepath = "/home/web/".$domain."/graphs";
	$graph["file"] = "$filepath/$filename";

	$result = line_graph($graph);
	{
		if ($result != 1)
		{
			$error_msg = $result;
		}
	}
?>
