<div class="title" colspan="2">Record Calibration Data</div>
<?	if ($page->error) { ?>
<div class="form_error" colspan="2"><?=$page->error?></div>
<?	} ?>
<?	if ($page->success) { ?>
<div class="form_success" colspan="2"><?=$page->success?></div>
<?	} ?>
<div class="form_instruction">Enter your calibration information here.  You have <?=$available_credits?> credits available.</div>
<table class="body" style="width: 600px;">
<form method="post" name="calibrationForm" action="/_spectros/calibrate">
<input type="hidden" name="code" value="<?=$asset->code?>" />
<input type="hidden" name="product" value="<?=$asset_product->code?>" />
<input type="hidden" name="date_calibration" value="<?=$date_calibration?>" />
<tr><td class="label">Monitor</td>
	<td class="label">Date</td>
</tr>
<tr><td class="value"><?=$asset->code?></td>
	<td class="value"><?=$date_calibration?></td>
</tr>
<tr><td class="label">Standard Manufacturer</td>
	<td class="label">Standard Concentration</td>
</tr>
<tr><td class="value"><input type="text" name="standard_manufacturer" class="value input" /></td>
	<td class="value"><input type="text" name="standard_concentration" class="value input" /></td>
</tr>
<tr><td class="label">Standard Expires</td>
	<td class="label">Cylinder Number</td>
</tr>
<tr><td class="value"><input type="text" name="standard_expires" class="value input" /></td>
	<td class="value"><input type="text" name="cylinder_number" class="value input" /></td>
</tr>
<tr><td class="label">Monitor Reading</td>
	<td class="label">Detector Voltage</td>
</tr>
<tr><td class="value"><input type="text" name="monitor_reading" class="value input" /></td>
	<td class="value"><input type="text" name="detector_voltage" class="value input" /></td>
</tr>
<tr><td class="form_footer" colspan="2" style="text-align: center"><input type="submit" name="btn_submit" value="Record" /></td></tr>
</form>
</table>