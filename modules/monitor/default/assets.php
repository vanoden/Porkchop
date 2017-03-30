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
<tr><td class="label columnLabel columnLabelLeft serialNumberLabel">Serial Number</td>
	<td class="label columnLabel zonesLabel">Zones</td>
	<td class="label columnLabel modelLabel">Model</td>
	<td class="label columnLabel columnLabelRight nameLabel">Name</td>
</tr>
<?	$greenbar = '';
	foreach ($assets as $asset) {
?>
<tr><td class="value columnValue columnValueLeft <?=$greenbar?>"><a href="/_monitor/asset/<?=$asset->id?>"><?=$asset->code?></a></td>
<?	app_log("Counting sensors",'debug',__FILE__,__LINE__); ?>
	<td class="value columnValue <?=$greenbar?>"><?=count($asset->sensors())?></td>
<?	app_log("Showing product code",'debug',__FILE__,__LINE__); ?>
	<td class="value columnValue <?=$greenbar?>"><?=$asset->product->code?></td>
<?	app_log("Showing asset name",'debug',__FILE__,__LINE__); ?>
	<td class="value columnValue columnValueRight <?=$greenbar?>"><?=$asset->name?></td>
<?	app_log("Finished line",'debug',__FILE__,__LINE__); ?>
</tr>
<?
		if ($greenbar) $greenbar = '';
		else $greenbar = "greenbar";
	} ?>
<tr><td colspan="4" class="table_footer"></td></tr>
</table>
<? app_log("template completed"); ?>