<?php
	$page->showBreadCrumbs();
	$page->showMessages();
?>
<style>
	.table {
		display: table;
		width: 756px;
	}
	.tableHead {
		display: table-row;
		font-weight: bold;
		text-align: center;
	}
	.tableRow {
		display: table-row;
	}
	.tableCell {
		display: table-cell;
		padding: 3px 10px;
		border: 1px solid #999999;
	}
</style>
<div class="table">
	<div class="tableHead">
		<div class="tableCell">Name</div>
		<div class="tableCell">Description</div>
		<div class="tableCell">Active Version</div>
	</div>
<?php	foreach ($termsOfUse as $tou) { ?>
	<div class="tableRow">
		<div class="tableCell"><a href="/_site/term_of_use?id=<?=$tou->id?>"><?=$tou->name?></a></div>
		<div class="tableCell"><?=$tou->description?></div>
		<div class="tableCell"><?=$tou->latestVersion()->id ?: 'none'?></div>
	</div>
<?php	} ?>
</div>
<a href="/_site/term_of_use" class="button">Add Terms of Use</a>