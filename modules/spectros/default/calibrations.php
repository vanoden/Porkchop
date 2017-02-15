<table class="body" style="width: 800px">
<tr><td class="title" colspan="5">Calibrations for <?=$asset->code?></td></tr>
<tr><td class="label">Date</td>
	<td class="label">Manufacturer</td>
	<td class="label">Cylinder</td>
	<td class="label">Concentration</td>
	<td class="label">Reading</td>
	<td class="label">Voltage</td>
</tr>
<?	foreach ($verifications as $verification) { ?>
<tr><td class="value"><?=$verification->date_request?></td>
	<td class="value"><?=$verification->standard_manufacturer?></td>
	<td class="value"><?=$verification->cylinder_number?></td>
	<td class="value"><?=$verification->standard_concentration?></td>
	<td class="value"><?=$verification->monitor_reading?></td>
	<td class="value"><?=$verification->detector_voltage?></td>
</tr>
<?	} ?>
</table>