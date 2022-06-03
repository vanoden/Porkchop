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
	<section id="form-message">
	<ul class="connectBorder errorText">
		<li><?=$page->errorString()?></li>
	</ul>
</section>
<?php	 } ?>

<h2>Monitors [<?=count($assets)?>]</h2>

<div class="tableBody bandedRows">
	<div class="tableRowHeader">
		<div class="tableCell">Serial Number</div>
		<div class="tableCell">Zones</div>
		<div class="tableCell">Model</div>
		<div class="tableCell">Name</div>
	</div> <!-- end row header -->
	<?php	$greebar = '';
		foreach ($assets as $asset) {
				if ($greenbar) $greenbar = ''; else $greenbar = "greenbar";
	?>
	<div class="tableRow">
		<div class="tableCell <?=$greenbar?>"><span class="value"><a href="/_monitor/asset/<?=$asset->code?>"><?=$asset->code?></a></span></div>
		<div class="tableCell <?=$greenbar?>"><span class="value"><?=$asset->sensorCount()?></span></div>
		<div class="tableCell <?=$greenbar?>"><span class="value"><?=$asset->product->code?></span></div>
		<div class="tableCell <?=$greenbar?>"><span class="value"><?=$asset->name?></span></div>
	</div>
	<?php	} ?>
</div>
