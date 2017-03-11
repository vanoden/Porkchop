<table class="body">
<tr><th class="label">Code</th><th class="label">Name</th><th class="label">Units</th><th class="label">Data Type</th><th class="label">Calculation Type</th><th class="label">Calibration Offset</th><th class="label">Calibration Multiplier</th></tr>
<?	foreach ($models as $model) { ?>
<tr><td class="value"><a href="/_monitor/model/<?=$model->code?>"><?=$model->code?></a></td><td class="value"><?=$model->name?></td><td class="value"><?=$model->units?></td><td class="value"><?=$model->data_type?></td><td class="value"><?=$model->calculation_type?></td><td class="value"><?=$model->calibration_offset?></td><td class="value"><?=$model->calibration_multiplier?></td></tr>
<?	} ?>
</table>