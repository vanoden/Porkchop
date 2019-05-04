<div class="title">Sensor</div>
<form name="sensorForm" method="post" action="/_monitor/admin_sensor">
<input type="hidden" name="id" value="<?=$id?>" />
<div class="container">
	<span class="label">Name</span>
	<input class="value input" name="name" value="<?=$name?>" />
</div>
<div class="container">
	<span class="label">Measures</span>
	<input class="value input" name="measures" value="<?=$measures?>" />
</div>
<div class="container">
	<span class="label">Units</span>
	<input class="value input" name="units" value="<?=$units?>" />
</div>
</form>
