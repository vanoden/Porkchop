<?php  if ($error_msg) { ?>
<table class="error"><tr><td class="error"><?=$error_msg?></td></tr></table>
<?php  }
	else
	{
?>
<table>
<tr><td class="heading_2" style="text-align:center"><?=$event_info["name"]?></td></tr>
<tr><td class="copy_3"><img src="/graphs/<?=$filename?>?rand=<?=time()?>"></td></tr>
<tr><td><table>
		<tr><td><table style="border: 1px solid black; width:475px; height: 220px;">
				<tr><th colspan="4">Event Information</th></tr>
				<tr><td class="copy_3">Start Fumigation</td>
					<td class="copy_3"><?=$event_info["date_start"]?></td>
					<td class="copy_3">Location</td>
					<td class="copy_3"><?=$event_info["location"]?></td>
				</tr>
				<tr><td class="copy_3">Fumigant Gas</td>
					<td class="copy_3"><?=$event_info["custom_3"]?></td>
					<td class="copy_3">Dose g/m3</td>
					<td class="copy_3"><?=$event_info["custom_4"]?></td>
				</tr>
				<tr><td class="copy_3">Fumigated Product</td>
					<td class="copy_3"><?=$event_info["custom_1"]?></td>
					<td class="copy_3">Temperature</td>
					<td class="copy_3"><?=$event_info["custom_2"]?></td>
				</tr>
				<tr><td class="copy_3">Comments</td>
					<td class="copy_3" colspan="3"><?=$event_info["notes"]?></td>
				</tr>
				</table>
			</td>
			<td><table style="border: 1px solid black; width: 175px; height: 220px">
				<tr><th colspan="3">Key</th></tr>
				<tr><td class="heading_3">Zone</td>
					<td class="heading_3">Last X</td>
					<td class="heading_3">Last Y</td>
				<?php	for ($zone = 0; $zone < 4; $zone ++) { ?>
				<tr><td class="copy_3" style="color: #<?=$graph["color"]["data"][$zone]?>"><?=$graph["data"][$zone]["label"]?></td>
					<td class="copy_3"><?=sprintf("%8.2f",$last_x[$zone])." ".$graph["axis"]["horizontal"]["units"]?></td>
					<td class="copy_3"><?=$last_y[$zone]." ".$graph["axis"]["vertical"]["units"]?></td>
				</tr>
				<?php	} ?>
				</table>
			</td>
		</tr>
		</table>
	</td>
</tr>
</table>
<?php  } ?>
