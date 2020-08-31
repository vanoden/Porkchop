<div class="tableBody">
	<div class="tableRowHeader">
			<div class="tableCell">Code</div>
			<div class="tableCell">Name</div>
			<div class="tableCell">Units</div>
			<div class="tableCell">Data Type</div>
			<div class="tableCell">Calculation Type</div>
			<div class="tableCell">Calculation Parameters</div>
			<div class="tableCell">Calibration Offset</div>
			<div class="tableCell">Calibration Modifier</div>
	</div>
<?php	foreach ($models as $model) { ?>
	<div class="tableRow">
			<div class="tableCell"><a href="/_monitor/sensor_model?code=<?=$model->code?>"><?=$model->code?></a></div>
			<div class="tableCell"><?=$model->name?></div>
			<div class="tableCell"><?=$model->units?></div>
			<div class="tableCell"><?=$model->data_type?></div>
			<div class="tableCell"><?=$model->calculation_type?></div>
			<div class="tableCell"><?=$model->calculation_parameters?></div>
			<div class="tableCell"><?=$model->calibration_offset?></div>
			<div class="tableCell"><?=$model->calibration_modifier?></div>
	</div>
<?php	} ?>
	<div class="tableRowFooter">
		<div class="tableCell" style="width: 15%">
			<a class="button" href="/_monitor/sensor_model">New Model</a>
			<hr style="clear: both; visibility: hidden">
		</div>
	</div>
</div>
