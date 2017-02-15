    <script language="Javascript">
        function goUrl(url)
        {
            location.href = url;
            return true;
        }
        function goMethodUrl(url)
        {
            location.href = '/_monitor/api/&amp;method='+url;
            return true;
        }
		function goCalibrationVerification()
		{
			document.mainform.action = '/_monitor/calibrate';
			document.mainform.code.value = '<?=$asset->code?>';
			document.mainform.submit();
		}
		function submitForm(assetID)
		{
			document.mainform.action = '/_monitor/details';
			document.mainform.id.value=assetID;
			document.mainform.submit();
			return true;
		}
    </script>
<form name="mainform" method="post">
<input type="hidden" name="code"/>
<input type="hidden" name="method"/>
            <table>
			<tr><td class="title" colspan="3">Asset Details</td></tr>
            <tr><td class="label" style="float: left; width: 70px;">Code</td>
				<td class="label" style="float: left; width: 190px;">Name</td>
				<td class="label" style="float: left; width: 100px;">Model</td>
			</tr>
            <tr><td class="value" style="float: left; width: 70px;"><input type="text" name="code" class="value input <?=$disabled_new?>" value="<?=$asset->code?>" /></td>
				<td class="value" style="float: left; width: 190px;"><input type="text" name="name" class="value input <?=$disabled?>" value="<?=$asset->name?>" /></td>
				<td class="value" style="float: left; width: 100px;"><select name="product_code" class="value input <?=$disabled?>">
						<option value="">Select</option>
						<option value="<?=$asset->product?>"><?=$asset->product?></option>
					</select>
				</td>
			</tr>
			</table>
			<table>
			<tr><td class="title" colspan="4">Asset Sensors</td></tr>
			<tr><td class="label">Code</td>
				<td class="label">Name</td>
				<td class="label">Units</td>
				<td class="label">Last Value</td>
				<td class="label">Last Read</td>
			</tr>
<?	foreach ($sensors as $sensor) { ?>
			<tr><td class="value"><input type="text" name="sensor_code" class="value input" value="<?=$sensor->code?>" <?=$disabled?> /></td>
				<td class="value"><input type="text" name="name" class="value input" value="<?=$sensor->name?>" <?=$disabled?> /></td>
				<td class="value"><input type="text" name="units" class="value input" value="<?=$sensor->units?>" <?=$disabled?> /></td>
				<td class="value"><input type="text" name="value" class="value input" value="<?=$sensor->value?>" disabled /></td>
				<td class="value"><input type="text" name="read" class="value input" value="<?=$sensor->date_read?>" disabled /></td>
			</tr>
<?	} ?>
			</table>
			<table>
			<tr><td class="title">Calibration History</td></tr>
			<tr><td class="label">Date Request</td>
				<td class="label">Std. Vendor</td>
				<td class="label">Std. Concentration</td>
				<td class="label">Actual Reading</td>
			</tr>
<?	foreach ($calibrations as $calibration) { ?>
			<tr><td class="value"><?=$calibration->date_request?></td>
				<td class="value"><?=$calibration->custom_1?></td>
				<td class="value"><?=$calibration->custom_2?></td>
				<td class="value"><?=$calibration->custom_3?></td>
			</tr>
<?	} ?>
			<tr><td colspan="4"><input type="button" name="btn_calibrate" class="value input" value="Verify Calibration" onclick="goCalibrationVerification()" /></td></tr>
			</table>
</form>
