<? if ($GLOBALS['_page']->error) { ?>
<div class="form_error"><?=$GLOBALS['_page']->error?></div>
<?	} ?>
<table class="body" style="width: 800px">
<tr><td class="title" colspan="6">Calibrations for <?=$asset->code?></td></tr>
<tr><td class="label">Date</td>
	<td class="label">Manufacturer</td>
	<td class="label">Cylinder</td>
	<td class="label">Concentration</td>
	<td class="label">Reading</td>
	<td class="label">Voltage</td>
</tr>
<?	foreach ($verifications as $verification) { ?>
<tr><td class="value"><?=$verification->date_request?></td>
	<td class="value"><?=$verification->getMetadata("standard_manufacturer")?></td>
	<td class="value"><?=$verification->getMetadata("cylinder_number")?></td>
	<td class="value"><?=$verification->getMetadata("standard_concentration")?></td>
	<td class="value"><?=$verification->getMetadata("monitor_reading")?></td>
	<td class="value"><?=$verification->getMetadata("detector_voltage")?></td>
</tr>
<?	} ?>
</table>
