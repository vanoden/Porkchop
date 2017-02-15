<form name="mainForm" method="post" action="calibrate">
<input type="hidden" name="todo" value="insert">
<input type="hidden" name="code" value="<?=$asset->code?>">
<table class="body">
<tr><th align="left" class="title">Device Calibration Check <span style="font-weight: normal">You have <?=$available?> credits available</span></td></tr>
<?	if ($GLOBALS['_page']->success) { ?>
<tr><td align="left" class="form_success"><?=$GLOBALS['_page']->success?></td></tr>
<?	} ?>
<?	if ($GLOBALS['_page']->error) { ?>
<tr><td align="left" class="form_error"><?=$GLOBALS['_page']->error?></td></tr>
<?	} ?>
</table>
<table class="body" cellpadding="0" cellspacing="0">
<tr><td class="title" colspan="6">Monitor</td></tr>
<tr><th align="left" class="label">Product</td>
	<td align="left" class="value" style="width:150px"><?=$product->code?></td>
	<th align="left" class="label">Label</td>
	<td align="left" class="value" style="width:150px"><?=$asset->name?></td>
	<th align="left" class="label">Serial</td>
	<td align="left" class="value" style="width:150px"><?=$asset->code?></td></tr>
</table>
<table class="body">
<tr><td class="title" colspan="4">Calibration Details</td></tr>
<tr><td align="left" class="label">Date</td>
	<td align="left" class="label">Standard Manufacturer</td>
	<td align="left" class="label">Standard Concentration</td>
	<td align="left" class="label">Monitor Reading</td>
</tr>
<tr><td align="center" class="value"><input type="text" name="date_request" size="15" class="input" value="<?=date("m/d/Y")?>"></td>
	<td align="left" class="value"><input type="text" name="custom_1" size="14" class="value input"></td>
	<td align="right" class="value"><input type="text" name="custom_2" size="8" class="value input"></td>
	<td align="left" class="value"><input type="text" name="custom_3" size="8" class="value input"></td>
</tr>
<tr><th align="left" class="label">Cylinder #</th>
	<th align="left" class="label">Standard Valid Until</th>
	<th align="left" class="label" colspan="2">Detector Voltage</th>
</tr>
<tr><td align="left" class="value"><input type="text" name="custom_4" size="14" class="value input"></td>
	<td align="right" class="value"><input type="text" name="custom_5" size="8" class="value input"></td>
	<td align="left" class="value" colspan="2"><input type="text" name="custom_6" size="8" class="value input"></td>
</tr>
<tr><td colspan="4" class="form_footer"><input type="submit" name="btn_submit" value="Save" class="button"></td></tr>
</table>
<table class="body" cellpadding="0" cellspacing="0">
<tr><td colspan="5" class="title">Previous Calibration Checks</td></tr>
<tr><th align="left" class="label">Date</th>
	<th align="left" class="label">Admin</th>
	<th align="left" class="label">Calib. Manu.</th>
	<th align="left" class="label">Calib. Conc.</th>
	<th align="left" class="label">Actual Reading</th>
</tr>
<?	# Loop Through Verification History
	foreach ($verifications as $verification)
	{
?>
<tr><td align="center" class="value"><?=$verification->date_request?></td>
	<td align="left" class="value"><?=$verification->customer?></td>
	<td align="left" class="value"><?=$verification->custom_1?></td>
	<td align="right" class="value"><?=$verification->custom_2?></td>
	<td align="right" class="value"><?=$verification->custom_3?></td>
</tr>
<?	} ?>
</form>
</table>

        </div>
    </div>
</div>
</form>