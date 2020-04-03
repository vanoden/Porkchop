<div class="breadcrumbs">
<a href="/_engineering/home">Engineering</a> > Releases
</div>
<?php include(MODULES.'/engineering/partials/search_bar.php'); ?>
<h2 style="display: inline-block;">Releases</h2>
<a class="button more" href="/_engineering/release">New Release</a>
<?	if ($page->errorCount()) { ?>
<div class="form_error"><?=$page->errorCount()?></div>
<?	}
	if ($page->success) { ?>
<div class="form_success"><?=$page->success?></div>
<?	} ?>
<!--	START First Table -->
	<div class="tableBody min-tablet">
	<div class="tableRowHeader">
		<div class="tableCell" style="width: 20%;">Code</div>
		<div class="tableCell" style="width: 40%;">Title</div>
		<div class="tableCell" style="width: 15%;">Status</div>
		<div class="tableCell" style="width: 25%;">Released On</div>
	</div>
<?php
	foreach ($releases as $release) {
?>
	<div class="tableRow">
		<div class="tableCell">
			<a href="/_engineering/release/<?=$release->code?>"><?=$release->code?></a>
		</div>
		<div class="tableCell">
			<?=$release->title?>
		</div>
		<div class="tableCell">
			<?=$release->status?>
		</div>
		<div class="tableCell">
			<?=$release->date_released?>
		</div>
	</div>
<?php	} ?>
</div>
<!--	END First Table -->
