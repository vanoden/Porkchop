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
<?	app_log("Counting sensors",'debug',__FILE__,__LINE__); ?>
	<td class="value <?=$greenbar?>"><?=count($asset->sensors())?></td>
<?	app_log("Showing product code",'debug',__FILE__,__LINE__); ?>
	<td class="value <?=$greenbar?>"><?=$asset->product->code?></td>
<?	app_log("Showing asset name",'debug',__FILE__,__LINE__); ?>
	<td class="value <?=$greenbar?>"><?=$asset->name?></td>
<?	app_log("Finished line",'debug',__FILE__,__LINE__); ?>
</tr>
<?
		if ($greenbar) $greenbar = '';
		else $greenbar = "greenbar";
	} ?>
</table>
<? app_log("template completed"); ?>