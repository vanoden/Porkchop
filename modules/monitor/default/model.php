<?	if ($this->error) { ?>
<div class="form_error"><?=$this->error?></div>
<?	}
	if ($this->success) { ?>
<div class="form_success"><?=$this->success?></div>
<?	} ?>
<form action="/_monitor/model" method="post">
<input type="hidden" name="id" value="<?=$model->id?>" />
<div class="question">
	<div class="label">Code</div>
	<div class="value"><input type="text" class="value input" name="code" value="<?=$model->code?>" /></div>
</div>
<div class="question">
	<div class="label">Name</div>
	<div class="value"><input type="text" class="value input" name="name" value="<?=$model->name?>" /></div>
</div>
<div class="question">
	<div class="label">Units</div>
	<div class="value"><input type="text" class="value input" name="units" value="<?=$model->units?>" /></div>
</div>
<div class="question">
	<div class="label">Calculation Type</div>
	<div class="value"><input type="text" class="value input" name="calculation_type" value="<?=$model->calculation_type?>" /></div>
</div>
<div class="question">
	<div class="label">Calibration Multiplier</div>
	<div class="value"><input type="text" class="value input" name="calibration_multiplier" value="<?=$model->calibration_multiplier?>" /></div>
</div>
<div class="question">
	<div class="label">Calibration Offset</div>
	<div class="value"><input type="text" class="value input" name="calibration_offset" value="<?=$model->calibration_offset?>" /></div>
</div>
<div class="question">
	<input type="submit" name="btn_submit" class="button" value="submit" />
</div>
</form>