<?php
	if ($todi == "add_hub")
	{
		# Prepare Query
		$add_hub_query = "
			INSERT
			INTO	monitor.event_hubs
			(		event_id,hub_id)
			VALUES
			(		'$id','$hub_id')
			";

		# Execute Query
		mysql_query($add_hub_query) or record_error(mysql_errno(),mysql_error(),$add_hub_query);
		add_xlog('monitor_events',$id);

	}
	if ($todi == "add_monitor")
	{
		# Prepare Query
		$add_monitor_query = "
			INSERT
			INTO	monitor.event_monitors
			(		event_id,hub_id,monitor_id)
			VALUES
			(		'$id','$hub_id','$monitor_id')
			";

		# Execute Query
		mysql_query($add_monitor_query) or record_error(mysql_errno(),mysql_error(),$add_monitor_query);
		add_xlog('monitor_events',$id);
	}
	if ($todi)
	{
		###############################################
		### Loop Through Zones and Update Locations	###
		###############################################
		# Get Event Hubs
		$hubs = get_hubs($id);

		# Loop Through Hubs
		while ($hub_info = mysql_fetch_array($hubs))
		{
			print "<!-- Hub: ".$hub_info["hub_id"]."-->\n";
			# Get Event/Hub Monitors
			$monitors = get_event_monitors($id,$hub_info["hub_id"]);

			# Loop Through Monitors
			while ($monitor_info = mysql_fetch_array($monitors))
			{
				print "<!-- Monitor: ".$monitor_info["monitor_id"]."-->\n";
				# Get Zones
				$zones = get_event_points($id,$monitor_info["monitor_id"]);

				# Loop Through Zones
				while ($zone_info = mysql_fetch_array($zones))
				{
					print "<!-- Zone: ".$zone_info["point_id"]."-->\n";

					# Fetch Location Name
					$sql_location = mysql_escape_string($location[$zone_info["point_id"]]);

					# Update Location Name
					if ($zone_info["present"])
					{
						$update_zone_query = "
							UPDATE	monitor.event_points
							SET		location = '$sql_location'
							WHERE	event_id = '$id'
							AND		point_id = '".$zone_info["point_id"]."'
							";

						print "<!-- Query:\n$update_zone_query\n-->\n";
					}
					else
					{
						$update_zone_query = "
							INSERT
							INTO	monitor.event_points
							(		event_id,point_id,location)
							VALUES
							(		'$id','".$zone_info["point_id"]."','$sql_location')
							";
					}
					add_xlog('monitor_events',$id);	
					# Execute Query
					mysql_query($update_zone_query) or record_error(mysql_errno(),mysql_error(),$update_zone_query);
				}
			}
		}
	}
