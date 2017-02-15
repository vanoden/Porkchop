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
<table class="body" cellpadding="0" cellspacing="0">
<tr><td colspan="4" class="title">Assets [<?=count($assets)?>]</td></tr>
<tr><td class="label serialNumberLabel">Serial Number</td>
	<td class="label zonesLabel">Zones</td>
	<td class="label modelLabel">Model</td>
	<td class="label nameLabel">Name</td>
</tr>
<?	$greenbar = '';
	foreach ($assets as $asset) {
?>
<tr><td class="value <?=$greenbar?>"><a href="/_monitor/asset/<?=$asset->id?>"><?=$asset->code?></a></td>
	<td class="value <?=$greenbar?>"><?=count($asset->sensors())?></td>
	<td class="value <?=$greenbar?>"><?=$asset->product->code?></td>
	<td class="value <?=$greenbar?>"><?=$asset->name?></td>
</tr>
<?
		if ($greenbar) $greenbar = '';
		else $greenbar = "greenbar";
	} ?>
</table>
