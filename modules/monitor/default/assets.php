<style>
	td.label {
		width: 110px;
	}
	td.serialNumberLabel {
		width: 200px;
	}
	td.modelLabel {
		width: 250px;
	}
	td.nameLabel {
		width: 280px;
	}
</style>
<?php	 if ($page->errorCount() > 0) { ?>
    <div class="form_error"><?=$page->errorString()?></div>
<?php	 } ?>
<div class="title">Monitors [<?=count($assets)?>]</div>
<table class="body" cellpadding="0" cellspacing="0">
<tr><th class="label serialNumberLabel">Serial Number</th>
	<th class="label zonesLabel">Zones</th>
	<th class="label modelLabel">Model</th>
	<th class="label nameLabel">Name</th>
</tr>
<?php	$greebar = '';
	foreach ($assets as $asset) {
?>
<tr><td class="value <?=$greenbar?>"><a href="/_monitor/asset/<?=$asset->code?>"><?=$asset->code?></a></td>
	<td class="value <?=$greenbar?>"><?=$asset->sensorCount()?></td>
	<td class="value <?=$greenbar?>"><?=$asset->product->code?></td>
	<td class="value <?=$greenbar?>"><?=$asset->name?></td>
</tr>
<?
		if ($greenbar) $greenbar = '';
		else $greenbar = "greenbar";
	} ?>
</table>
