<div class="tableTitle">Sensors</div>
<div class="tableBody">
	<div class="tableColumn"></div>
	<div class="tableColumn"></div>
	<div class="tableColumn"></div>
	<div class="tableRowHeader">
		<div class="tableCell">Name</div>
		<div class="tableCell">Measures</div>
		<div class="tableCell">Units</div>
	</div>
<?	foreach ($sensors as $sensor) { ?>
	<div class="tableRow">
		<div class="tableCell sensorsColumnName"><?=print_r($sensor,true)?></div>
		<div class="tableCell sensorsColumnMeasures"></div>
		<div class="tableCell sensorsColumnUnits"></div>
	</div>
<?	} ?>
</div>
<div class="form_footer">
	<input type="button" name="btn_add_sensor" class="button" value="Add Sensor" onclick="window.location.href='/_monitor/admin_sensor';"/>
</div>
