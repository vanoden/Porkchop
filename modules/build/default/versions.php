<h2>Build Versions</h2>
<div class="tableBody">
	<div class="tableRowHeader">
		<div class="tableCell">Build Number</div>
		<div class="tableCell">Timestamp</div>
		<div class="tableCell">Status</div>
		<div class="tableCell">Tarball</div>
		<div class="tableCell">Built By</div>
	</div>
<?php	foreach ($versions as $version) {
	$user = $version->user();
?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_build/version?id=<?=$version->id?>"><?=sprintf("%0d.%0d.%0d",$version->major_number,$version->minor_number,$version->number)?></a></div>
		<div class="tableCell"><?=$version->timestamp?></div>
		<div class="tableCell"><?=$version->status?></div>
		<div class="tableCell"><?=$version->tarball?></div>
		<div class="tableCell"><?=$user->full_name()?></a></div>
	</div>
<?php	} ?>
</div>
