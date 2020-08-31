<form method="post" name="sensorModelForm">
<input type="hidden" name="id" value="<?=$model->id?>" />
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Code</div>
		<div class="tableCell">Name</div>
		<div class="tableCell">Units</div>
		<div class="tableCell">Data Type</div>
	</div>
	<div class="tableRow">
		<div class="tableCell"><input type="text" name="code" class="value input" value="<?=$model->code?>" /></div>
		<div class="tableCell"><input type="text" name="name" class="value input" value="<?=$model->name?>" /></div>
		<div class="tableCell"><input type="text" name="units" class="value input" value="<?=$model->units?>" /></div>
		<div class="tableCell"><select name="data_type" class="value input">
			<option value="decimal"<? if ($model->data_type == "decimal") print " selected";?>>decimal</div>
			<option value="integer"<? if ($model->data_type == "integer") print " selected";?>>integer</div>
			<option value="boolean"<? if ($model->data_type == "boolean") print " selected";?>>boolean</div>
			<option value="string"<? if ($model->data_type == "string") print " selected";?>>string</div>
			</select>
		</div>
	</div>
	<div class="tableRowHeader">
		<div class="tableCell">Calculation Type</div>
		<div class="tableCell">Calculation Parameters</div>
		<div class="tableCell">Calibration Offset</div>
		<div class="tableCell">Calibration Multiplier</div>
	</div>
	<div class="tableRow">
		<div class="tableCell"><input type="text" name="calculation_type" class="value input" value="<?=$model->calculation_type?>" /></div>
		<div class="tableCell"><input type="text" name="calculation_parameters" class="value input" value="<?=$model->calculation_parameters?>" /></div>
		<div class="tableCell"><input type="text" name="calibration_offset" class="value input" value="<?=$model->calibration_offset?>" /></div>
		<div class="tableCell"><input type="text" name="calibration_multiplier" class="value input" value="<?=$model->calibration_multiplier?>" /></div>
	</div>
	<div class="tableRowFooter">
		<div class="tableCell" colspan="4"><input type="submit" class="button" name="btn_submit" value="Submit" /></div>
	</div>
</div>
</form>
