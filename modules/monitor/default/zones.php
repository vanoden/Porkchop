		<script language="JavaScript">
		<!--
			function addHub()
			{
				var pullDown = document.zoneForm.add_hub;
				var hubId = pullDown.options[pullDown.selectedIndex].value;
				document.zoneForm.hub_id.value = hubId;
				document.zoneForm.todi.value = 'add_hub';
				document.zoneForm.submit();
				return true;
			}
			function addMonitor(hubId)
			{
				var pullDown = document.zoneForm.elements['add_monitor['+hubId+']'];
				var monitorId = pullDown.options[pullDown.selectedIndex].value;
				document.zoneForm.hub_id.value = hubId;
				document.zoneForm.monitor_id.value = monitorId;
				document.zoneForm.todi.value = 'add_monitor';
				document.zoneForm.submit();
				return true;
			}
			function updateForm()
			{
				document.zoneForm.todi.value = 'update';
				document.zoneForm.submit();
				return true;
			}
		//-->
		</script>
		<form name="zoneForm" action="/_monitor/zones/<?=$id?>" method="post">
		<input type="hidden" name="hub_id">
		<input type="hidden" name="monitor_id">
		<input type="hidden" name="todi" value="update">
		<table width="500" bgcolor="#666666" cellpadding="1" cellspacing="1">
		<tr><td class="heading_2" colspan="2">Monitored Zones<br><a href="/_monitor/event/<?=$id?>"><< Event</a></td></tr>
		<?PHP
			# Get Hubs Associated With Job
			$hubs = get_event_hubs($id);

			# Loop Through Hubs
			while ($hub_info = mysql_fetch_array($hubs))
			{
				# This Hub Already Associated
				$assoc_hubs[$hub_info["hub_id"]] = 1;
		?>
		<tr><td class="heading_3" colspan="2">Hub: <?=$hub_info["name"]?></td></tr>
		<?PHP
				# Get Monitors Associated With Hub
				$monitors = get_event_monitors($id,$hub_info["hub_id"]);

				# Loop through Assigned Monitors
				while ($monitor_info = mysql_fetch_array($monitors))
				{
					# This Monitor Already Associated
					$assoc_monitors[$monitor_info["monitor_id"]] = 1;
		?>
		<tr><td class="heading_2" colspan="2">Monitor: <?=$monitor_info["label"]?></td></tr>
		<tr><td class="heading_3">Zone</td>
			<td class="heading_3">Location</td>
		</tr>
		<?PHP
					# Get Zones For Monitor
					$zones = get_event_points($id,$monitor_info["monitor_id"]);

					# Loop Through Monitor Zones
					while ($zone_info = mysql_fetch_array($zones))
					{
		?>
		<tr><td class="copy_3"><?=$zone_info["zone"]?></td>
			<td class="copy_3"><input name="location[<?=$zone_info["point_id"]?>]" size="30" class="input" value="<?=$zone_info["location"]?>"></td></tr>
		<?PHP
					}
				}
		?>
		<tr><td colspan="2">Select Monitor To Add <select name="add_monitor[<?=$hub_info["hub_id"]?>]" class="input">
		<?PHP
				# Get All Monitors
				$monitors = get_monitors();

				# Loop Through Monitors
				while ($monitor_info = mysql_fetch_array($monitors))
				{
					if ($assoc_monitors[$monitor_info["monitor_id"]]) continue;
		?>
					<option value="<?=$monitor_info["monitor_id"]?>"><?=$monitor_info["label"]?></option>
		<?PHP
				}
		?>
				</select>
				<input type="button" name="btn_monitor" value="Add" onclick="addMonitor(<?=$hub_info["hub_id"]?>)">
			</td>
		</tr>
		<?PHP
			}
		?>
		<tr><td colspan="2">Select Hub To Add <select name="add_hub" class="input">
		<?PHP
			# Get All Hubs
			$hubs = get_hubs();

			# Loop Through Hubs
			while ($hub_info = mysql_fetch_array($hubs))
			{
				if ($assoc_hubs[$hub_info["hub_id"]]) continue;
		?>
					<option value="<?=$hub_info["hub_id"]?>"><?=$hub_info["name"]?></option>
		<?PHP
			}
		?>
				</select>
				<input type="button" name="btn_hub" value="Add" onclick="addHub()">
			</td>
		</tr>
		<tr><td colspan="2" align="center"><input type="submit" name="update" value="Update Locations" onclick="updateForm"></td></tr>
		</table>
		</form>
